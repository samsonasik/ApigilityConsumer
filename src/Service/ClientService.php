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
     * It call API for generic API Services
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
                
        if (! empty($data['form-data']['files'])) {
            
            $files = $data['form-data']['files'];
            foreach ($files as $key => $file) {
                if (empty($file['tmp_name']) || empty($file['name'])) {
                    $response = new Response();
                    $response->setStatusCode(SpecialErrorMessage::RESOURCE_NOT_AVAILABLE['code']);
                    $response->setReasonPhrase(sprintf(
                        SpecialErrorMessage::INVALID_REQUEST_FILE['reason'],
                        $this->apiHostUrl
                    ));
                    
                    return $this->getClientResult($response); 
                }
                
                $this->httpClient->setFileUpload($file['tmp_name'], $file['name']);
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
     * otherwise, will success.
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
        if ($statusCode !== 200 && $statusCode !== 422) { // 422 is Unprocessable Entity, will be handled in ClientResult
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
