<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Psr\Container\ContainerInterface;
use Zend\Http\Client as HttpClient;

class ClientAuthServiceFactory
{
    public function __invoke(ContainerInterface $container) : ClientAuthService
    {
        $config     = $container->get('config');
        $httpClient = new HttpClient();

        return new ClientAuthService(
            $config['apigility-consumer']['api-host-url'],
            $httpClient,
            $config['apigility-consumer']['oauth']
        );
    }
}
