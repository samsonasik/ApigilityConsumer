<?php

namespace ApigilityConsumer\Service;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientResult;
use RuntimeException;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response;
use Zend\Json\Json;

class ClientService implements ClientApiInterface
{
    /** @var string */
    private $apiHostUrl;

    /** @var HttpClient */
    private $httpClient;

    public function __construct($apiHostUrl, HttpClient $httpClient)
    {
        $this->apiHostUrl = $apiHostUrl;
        $this->httpClient = $httpClient;
    }

    /**
     * {inheritdoc}.
     *
     * It call API for generic card actions, search, create card and bulk actions
     *
     * @param array    $data
     * @param int|null $timeout
     *
     * @return ClientResult
     */
    public function callAPI(array $data, $timeout = null)
    {
        $headers = [];

        if (isset($data['token_type']) && isset($data['access_token'])) {
            $headers = [
                'Authorization' => $data['token_type'].' '.$data['access_token'],
            ];
        }

        $headers += [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ];
        $this->httpClient->setRawBody(Json::encode($data['form-data']));

        if (null !== $timeout) {
            $timeout = (int) $timeout;
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
     * otherwise, depends on responseCode returned in ['response']['responseCode'] key on processed $response->getBody()
     *
     * @param Response $response
     *
     * @return ClientResult
     */
    private function getClientResult(Response $response)
    {
        $messages = ClientResult::$messages;

        if ($response->getBody() === '') {
            $messages = [
                'http' => [
                    SpecialErrorMessage::INVALID_REQUEST['code'] => SpecialErrorMessage::INVALID_REQUEST['reason'],
                ],
            ];
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200 && $statusCode !== 422) {
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
