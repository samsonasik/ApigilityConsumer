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
class ClientAuthServiceFactory
{
    public function __invoke(ServiceLocatorInterface $container) : ClientAuthService
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
