<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Service\ClientAuthService;
use ApigilityConsumer\Service\ClientAuthServiceFactory;
use Kahlan\Plugin\Double;
use Psr\Container\ContainerInterface;

describe('ClientAuthServiceFactory', function (): void {
    beforeAll(function (): void {
        $this->factory = new ClientAuthServiceFactory();
    });

    describe('->__invoke', function (): void {

        it('return "ClientAuthService" instance', function (): void {

            $container = Double::instance(['implements' => ContainerInterface::class]);
            allow($container)->toReceive('get')->with('config')->andReturn([
                'apigility-consumer' => [
                    'api-host-url' => 'http://api.host.url',
                    'oauth' => [
                        'grant_type'    => 'password',
                        'client_id'     => 'foo',
                        'client_secret' => 'foo_s3cret',
                    ],
                ],
            ]);

            $result = $this->factory->__invoke($container);
            expect($result)->toBeAnInstanceOf(ClientAuthService::class);

        });

    });

});
