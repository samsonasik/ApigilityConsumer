<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientAuthResult;
use ApigilityConsumer\Result\ResultInterface;
use InvalidArgumentException;
use Laminas\Http\Client;
use Laminas\Http\Response;
use Laminas\Json\Json;
use RuntimeException;

use function in_array;
use function sprintf;

class ClientAuthService implements ClientApiInterface
{
    private ?string $client = null;

    public function __construct(private string $apiHostUrl, private Client $httpClient, private array $oauthConfig)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withClient(string $client): self
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
     */
    public function resetClient(): self
    {
        $this->client = null;
        return $this;
    }

    /**
     * {inheritdoc}.
     *
     * It call API for authentication process.
     *
     * @return ClientAuthResult
     */
    public function callAPI(array $data, ?int $timeout = null): ResultInterface
    {
        $headers = [
            'Accept'       => 'application/json',
            'Content-type' => 'application/json',
        ];

        $oauthConfig = $this->oauthConfig;
        if ($this->client !== null) {
            $oauthConfig              = $this->oauthConfig['clients'][$this->client];
            $oauthConfig['client_id'] = $this->client;
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
        $this->httpClient->setUri($this->apiHostUrl . $data['api-route-segment']);
        $this->httpClient->setMethod($data['form-request-method']);

        if (null !== $timeout) {
            $this->httpClient->setOptions(['timeout' => $timeout]);
        }

        try {
            $response = $this->httpClient->send();
        } catch (RuntimeException) {
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
     */
    private function getClientAuthResult(Response $response): ClientAuthResult
    {
        ClientAuthResult::$messages = [];
        $statusCode                 = $response->getStatusCode();
        $body                       = $response->getBody();

        if ($statusCode === Response::STATUS_CODE_200) {
            return ClientAuthResult::applyResult($body);
        }

        // 400 is specifically invalid request due missing request parameter passed or invalid client details
        // 401 is invalid grant ( username or password or both are invalid )
        $reasonPhrase = in_array($statusCode, [Response::STATUS_CODE_400, Response::STATUS_CODE_401], true)
            ? Json::decode($body, Json::TYPE_ARRAY)['detail']
            : $response->getReasonPhrase();

        ClientAuthResult::$messages = [
            'http' => [
                $statusCode => $reasonPhrase,
            ],
        ];

        return ClientAuthResult::applyResult($body);
    }
}
