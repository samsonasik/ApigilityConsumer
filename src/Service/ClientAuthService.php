<?php

namespace ApigilityConsumer\Service;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientAuthResult;
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
    private $authConfig;

    /**
     * ClientAuthService constructor.
     *
     * @param $apiHostUrl
     * @param HttpClient $httpClient
     * @param array      $authConfig
     */
    public function __construct($apiHostUrl, HttpClient $httpClient, array $authConfig)
    {
        $this->apiHostUrl = $apiHostUrl;
        $this->httpClient = $httpClient;
        $this->authConfig = $authConfig;
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

        $dataTobeSent = [
            'grant_type' => $this->authConfig['grant_type'],
            'username' => $data['form-data']['username'],
            'password' => $data['form-data']['password'],
            'client_id' => $this->authConfig['client_id'],
            'client_secret' => $this->authConfig['client_secret'],
        ];

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
            $response->setReasonPhrase(sprintf(
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
        $statusCode = $response->getStatusCode();
        if ($statusCode === 410) {
            ClientAuthResult::$messages = [
                'http' => [
                    $response->getStatusCode() => $response->getReasonPhrase(),
                ],
            ];
        }

        // 400 is specifically invalid request due missing request parameter passed
        // so, show user that login failed instead for security reason
        if ($statusCode !== 200 && $statusCode !== 400 && $statusCode !== 410) {
            ClientAuthResult::$messages = [
                'http' => [
                    $response->getStatusCode() => Json::decode($response->getBody(), true)['detail'],
                ],
            ];
        }

        return ClientAuthResult::applyResult($response->getBody());
    }
}