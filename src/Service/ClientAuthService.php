<?php

namespace ApigilityConsumer\Service;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientAuthResult;
use InvalidArgumentException;
use RuntimeException;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response;
use Zend\Json\Json;

class ClientAuthService implements ClientApiInterface
{
    /** @var  string */
    private $apiHostUrl;

    /** @var HttpClient */
    private $httpClient;

    /** @var array  */
    private $oauthConfig;

    /** @var string|null */
    private $client   = null;

    /**
     * ClientAuthService constructor.
     *
     * @param string     $apiHostUrl
     * @param HttpClient $httpClient
     * @param array      $oauthConfig
     */
    public function __construct($apiHostUrl, HttpClient $httpClient, array $oauthConfig)
    {
        $this->apiHostUrl = $apiHostUrl;
        $this->httpClient = $httpClient;
        $this->oauthConfig = $oauthConfig;
    }

    /**
     * @param  string     $client
     * @throws InvalidArgumentException
     * @return self
     */
    public function withClient($client = null)
    {
        if (! isset($this->oauthConfig['clients'][$client])) {
            throw new InvalidArgumentException('client selected not found in the "clients" config');
        }

        $this->client = $client;
        return $this;
    }

    /**
     * Reset client_id back to null,
     * for handle after callAPI() already called with specified client
     *
     * @return self
     */
    public function resetClient()
    {
        $this->client = null;
        return $this;
    }

    /**
     * {inheritdoc}.
     *
     * It call API for authentication process.
     *
     * @param array    $data
     * @param int|null $timeout
     *
     * @return ClientAuthResult
     */
    public function callAPI(array $data, $timeout = null)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-type' => 'application/json',
        ];

        $oauthConfig = $this->oauthConfig;
        if ($this->client !== null) {
            $oauthConfig = $this->oauthConfig['clients'][$this->client];
            $oauthConfig['client_id'] =  $this->client;
        }

        $dataTobeSent = [
            'grant_type'    => $oauthConfig['grant_type'],
            'client_id'     => $oauthConfig['client_id'],
            'client_secret' => $oauthConfig['client_secret'],
        ];

        if ($oauthConfig['grant_type'] !== 'client_credentials') {
            $dataTobeSent += [
                'username' => $data['form-data']['username'],
                'password' => $data['form-data']['password'],
            ];
        }

        $this->httpClient->setRawBody(Json::encode($dataTobeSent));

        $this->httpClient->setHeaders($headers);
        $this->httpClient->setUri($this->apiHostUrl.$data['api-route-segment']);
        $this->httpClient->setMethod($data['form-request-method']);

        if (null !== $timeout) {
            $timeout = (int) $timeout;
            $this->httpClient->setOptions(['timeout' => $timeout]);
        }

        try {
            $response = $this->httpClient->send();
        } catch (RuntimeException $e) {
            $response = new Response();
            $response->setStatusCode(SpecialErrorMessage::RESOURCE_NOT_AVAILABLE['code']);
            $response->setReasonPhrase(\sprintf(
                SpecialErrorMessage::RESOURCE_NOT_AVAILABLE['reason'],
                $this->apiHostUrl
            ));
        }

        return $this->getClientAuthResult($response);
    }

    /**
     * Handle return ClientAuthResult with 'success' or 'failure'.
     * when ClientAuthResult::$messages is not empty, or response status code is not 200 it will failure,.
     *
     * @param Response $response
     *
     * @return ClientAuthResult
     */
    private function getClientAuthResult(Response $response)
    {
        ClientAuthResult::$messages = [];
        $statusCode                 = $response->getStatusCode();

        if ($statusCode !== 200 && $statusCode !== 400 && $statusCode !== 401) {
            ClientAuthResult::$messages = [
                'http' => [
                    $statusCode => $response->getReasonPhrase(),
                ],
            ];
        }

        // 400 is specifically invalid request due missing request parameter passed or invalid client details
        // 401 is invalid grant ( username or password or both are invalid )
        if ($statusCode === 400 || $statusCode === 401) {
            ClientAuthResult::$messages = [
                'http' => [
                    $statusCode => Json::decode($response->getBody(), 1)['detail'],
                ],
            ];
        }

        return ClientAuthResult::applyResult($response->getBody());
    }
}
