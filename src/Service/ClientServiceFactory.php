<?php

namespace ApigilityConsumer\Service;

use Zend\Http\Client as HttpClient;
use Zend\Http\Client\Adapter\Curl;

/**
 * Factory to handle ClientService creation.
 *
 * Class ClientServiceFactory
 */
class ClientServiceFactory
{
    public function __invoke($container)
    {
        $config     = $container->get('config');
        $httpClient = new HttpClient(null, ['adapter' => Curl::class]);
        $authConfig = (!empty($config['apigility-consumer']['auth']))
            ? $config['apigility-consumer']['auth']
            : [];

        return new ClientService(
            $config['apigility-consumer']['api-host-url'],
            $httpClient,
            $authConfig
        );
    }
}
