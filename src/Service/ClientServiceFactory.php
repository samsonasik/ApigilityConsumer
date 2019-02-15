<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Psr\Container\ContainerInterface;
use Zend\Http\Client as HttpClient;

class ClientServiceFactory
{
    public function __invoke(ContainerInterface $container) : ClientService
    {
        $config     = $container->get('config');
        $httpClient = new HttpClient();
        $authConfig = $config['apigility-consumer']['auth'] ?? [];

        return new ClientService(
            $config['apigility-consumer']['api-host-url'],
            $httpClient,
            $authConfig
        );
    }
}
