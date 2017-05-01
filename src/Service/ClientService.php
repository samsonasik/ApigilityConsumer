<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientResult;
use ApigilityConsumer\Result\ResultInterface;
use InvalidArgumentException;
use RuntimeException;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response;
use Zend\Json\Json;
use Zend\Stdlib\ErrorHandler;

class ClientService implements ClientApiInterface
{
    /** @var string */
    private $apiHostUrl;

    /** @var HttpClient */
    private $httpClient;

    /** @var array  */
    private $authConfig;

    private $client   = null;
    private $authType = null;


    /**
     * ClientService constructor.
     *
     * @param  string      $apiHostUrl
     * @param  HttpClient  $httpClient
     * @param  array       $authConfig
     */
    public function __construct(
        $apiHostUrl,
        HttpClient $httpClient,
        array $authConfig
    ) {
        $this->apiHostUrl = $apiHostUrl;
        $this->httpClient = $httpClient;
        $this->authConfig = $authConfig;
    }

    /**
     * @param  string     $client
     * @throws InvalidArgumentException
     * @return self
     */
    public function withClient(string $client = null) : self
    {
        if (! isset($this->authConfig['clients'][$client])) {
            throw new InvalidArgumentException('client selected not found in the "clients" config');
        }

        $this->client = $client;
        return $this;
    }

    /**
     * Set Auth Type if required
     *
     * @param string $authType
     * @throws InvalidArgumentException
     * @return self
     */
    public function withHttpAuthType(string $authType = HttpClient::AUTH_BASIC) : self
    {
        if (! in_array($authType, [HttpClient::AUTH_BASIC, HttpClient::AUTH_DIGEST])) {
            throw new InvalidArgumentException('authType selected should be a ' . HttpClient::AUTH_BASIC . ' or ' . HttpClient::AUTH_DIGEST);
        }

        $this->authType = $authType;
        return $this;
    }

    /**
     * Reset Auth Type back to null,
     * for handle after callAPI() already called with specified auth type
     *
     * @return self
     */
    public function resetHttpAuthType() : self
    {
        $this->authType = null;
        return $this;
    }

    /**
     * Reset client_id back to null,
     * for handle after callAPI() already called with specified client
     *
     * @return self
     */
    public function resetClient() : self
    {
        $this->client = null;
        return $this;
    }

    /**
     * {inheritdoc}.
     *
     * It call API for generic API Services
     *
     * @param array    $data
     * @param int|null $timeout
     *
     * @return ClientResult
     */
    public function callAPI(array $data, int $timeout = null) : ResultInterface
    {
        $headers = [];

        if (isset($data['token_type']) && isset($data['access_token'])) {
            $headers = [
                'Authorization' => $data['token_type'].' '.$data['access_token'],
            ];
        }

        if ($this->authType !== null) {

            $authConfig = $this->authConfig;
            if ($this->client !== null) {
                $authConfig = $this->authConfig['clients'][$this->client];
            }

            $authConfigSelected = [];
            if (! empty($authConfig[$this->authType])) {
                $authConfigSelected = $authConfig[$this->authType];
            }

            if (! empty($data['auth'][$this->authType])) {
                $authConfigSelected = $data['auth'][$this->authType];
            }

            if (! empty($authConfigSelected['username']) && ! empty($authConfigSelected['password'])) {
                if ($this->authType === HttpClient::AUTH_DIGEST) {
                    $this->httpClient->setAdapter(Curl::class);
                }

                $this->httpClient->setAuth($authConfigSelected['username'], $authConfigSelected['password'], $this->authType);
            }
        }

        $headers += [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ];

        if (! empty($data['form-data']['files'])) {

            $files = $data['form-data']['files'];
            $fileIsValid = true;
            foreach ($files as $key => $file) {
                if (empty($file['tmp_name']) || empty($file['name'])) {
                    $fileIsValid = false;
                } else {
                    ErrorHandler::start();
                    $fileContent = file_get_contents($file['tmp_name']);
                    ErrorHandler::stop();
                    if ($fileContent === false) {
                        $fileIsValid = false;
                    }
                }

                if (! $fileIsValid) {
                    $response = new Response();
                    $response->setStatusCode(SpecialErrorMessage::RESOURCE_NOT_AVAILABLE['code']);
                    $response->setReasonPhrase(sprintf(
                        SpecialErrorMessage::INVALID_REQUEST_FILE['reason'],
                        $this->apiHostUrl
                    ));

                    return $this->getClientResult($response);
                }

                $this->httpClient->setFileUpload($file['tmp_name'], $key);
            }

            // no need to include in raw data
            unset($data['form-data']['files']);
            // remove Content-type definition
            unset($headers['Content-type']);
        }

        if (empty($data['form-data'])) {
            $data['form-data'] = [];
        }

        $this->httpClient->setRawBody(Json::encode($data['form-data']));

        if (null !== $timeout) {
            $this->httpClient->setOptions(['timeout' => $timeout]);
        }

        $this->httpClient->setHeaders($headers);
        $this->httpClient->setUri($this->apiHostUrl.$data['api-route-segment']);
        $this->httpClient->setMethod($data['form-request-method']);

        try {
            $response = $this->httpClient->send();
        } catch (RuntimeException $e) {
            $response = new Response();
            $response->setStatusCode(SpecialErrorMessage::RESOURCE_NOT_AVAILABLE['code']);
            $response->setReasonPhrase(sprintf(
                SpecialErrorMessage::RESOURCE_NOT_AVAILABLE['reason'],
                $this->apiHostUrl
            ));
        }

        return $this->getClientResult($response);
    }

    /**
     * Handle return ClientResult with 'success' or 'failure'.
     * when ClientResult::$messages is not empty, or response status code is not 200 ( messages will filled via ClientResult::$messages assignment ),
     * it will failure,.
     *
     * otherwise, will success.
     *
     * @param Response $response
     *
     * @return ClientResult
     */
    private function getClientResult(Response $response) : ClientResult
    {
        $messages = ClientResult::$messages;

        $statusCode = $response->getStatusCode();
        if ($statusCode === Response::STATUS_CODE_200 && $response->getBody() === '') {
            $messages = [
                'http' => [
                    Response::STATUS_CODE_204 => 'No Content',
                ],
            ];
        }

        if ($statusCode !== Response::STATUS_CODE_200 && $statusCode !== Response::STATUS_CODE_422) { // 422 is Unprocessable Entity, will be handled in ClientResult
            $messages = [
                'http' => [
                    $response->getStatusCode() => $response->getReasonPhrase(),
                ],
            ];
        }

        ClientResult::$messages = $messages;

        return ClientResult::applyResult($response->getBody());
    }
}
