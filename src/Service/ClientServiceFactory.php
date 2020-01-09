<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Laminas\Http\Client as HttpClient;
use Psr\Container\ContainerInterface;

class ClientServiceFactory
{
    public function __invoke(ContainerInterface $container): ClientService
    {
        $config     = $container->get('config')['apigility-consumer'];
        $apiHostURL = $config['api-host-url'];
        $httpClient = new HttpClient(null, $config['http_client_options'] ?? null);
        $authConfig = $config['auth'] ?? [];

        return new ClientService(
            $apiHostURL,
            $httpClient,
            $authConfig
        );
    }
}
