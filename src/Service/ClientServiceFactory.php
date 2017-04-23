<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Interop\Container\ContainerInterface;
use Zend\Http\Client as HttpClient;
use Zend\ServiceManager\Factory\FactoryInterface;

class ClientServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return ClientService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ClientService
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
