<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use Interop\Container\ContainerInterface;
use Zend\Http\Client as HttpClient;
use Zend\ServiceManager\Factory\FactoryInterface;

class ClientAuthServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     *
     * @return ClientAuthService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ClientAuthService
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
