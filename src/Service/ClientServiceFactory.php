<?php

namespace ApigilityConsumer\Service;

use Zend\Http\Client as HttpClient;

/**
 * Factory to handle ClientService creation.
 *
 * Class ClientServiceFactory
 */
class ClientServiceFactory
{
    public function __invoke($container)
    {
        $config = $container->get('config');
        $httpClient = new HttpClient();

        return new ClientService(
            $config['apigility-consumer']['api-host-url'],
            $httpClient
        );
    }
}
