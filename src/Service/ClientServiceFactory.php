<?php

namespace ApigilityConsumer\Service;

use Interop\Container\ContainerInterface;
use Zend\Http\Client as HttpClient;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * Factory to handle ClientService creation.
 *
 * Class ClientServiceFactory
 */
class ClientServiceFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $httpClient = new HttpClient();

        return new ClientService(
            $config['api-host-url'],
            $httpClient
        );
    }
}
