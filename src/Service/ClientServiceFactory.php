<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Zend\Http\Client as HttpClient;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to handle ClientService creation.
 *
 * Class ClientServiceFactory
 */
class ClientServiceFactory
{
    public function __invoke(ServiceLocatorInterface $container) : ClientService
    {
        $config     = $container->get('config');
        $httpClient = new HttpClient();
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
