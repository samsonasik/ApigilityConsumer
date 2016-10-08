<?php

namespace ApigilityConsumer\Service;

use Zend\Http\Client as HttpClient;

/**
 * Factory to handle ClientService creation.
 *
 * Class ClientServiceFactory
 */
class ClientAuthServiceFactory
{
    public function __invoke($container)
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
