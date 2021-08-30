<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Laminas\Http\Client;
use Psr\Container\ContainerInterface;

class ClientAuthServiceFactory
{
    public function __invoke(ContainerInterface $container): ClientAuthService
    {
        $config      = $container->get('config')['apigility-consumer'];
        $apiHostURL  = $config['api-host-url'];
        $httpClient  = new Client(null, $config['http_client_options'] ?? null);
        $oauthConfig = $config['oauth'];

        return new ClientAuthService(
            $apiHostURL,
            $httpClient,
            $oauthConfig
        );
    }
}
