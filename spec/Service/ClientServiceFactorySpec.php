<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Service\ClientService;
use ApigilityConsumer\Service\ClientServiceFactory;
use Kahlan\Plugin\Double;
use Psr\Container\ContainerInterface;

describe('ClientServiceFactory', function (): void {
    beforeAll(function (): void {
        $this->factory = new ClientServiceFactory();
    });

    describe('->__invoke', function (): void {

        it('return "ClientService" instance', function (): void {

            $container = Double::instance(['implements' => ContainerInterface::class]);
            allow($container)->toReceive('get')->with('config')->andReturn([
                'apigility-consumer' => [
                    'api-host-url' => 'http://api.host.url'
                ],
            ]);

            $result = $this->factory->__invoke($container);
            expect($result)->toBeAnInstanceOf(ClientService::class);

        });

    });

});
