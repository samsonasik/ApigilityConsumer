<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Service\ClientAuthService;
use ApigilityConsumer\Service\ClientAuthServiceFactory;
use Kahlan\Plugin\Double;
use Zend\ServiceManager\ServiceManager;

describe('ClientAuthServiceFactory', function () {
    beforeAll(function () {
        $this->factory = new ClientAuthServiceFactory();
    });
 
    describe('->__invoke', function () {
        it('return "ClientAuthService" instance', function () {
            $container = Double::instance(['extends' => ServiceManager::class]);
            allow($container)->toReceive('get')->with('config')->andReturn([
                'api-host-url' => 'http://api.host.url',
                'oauth' => [
                    'grant_type'    => 'password',
                    'client_id'     => 'foo',
                    'client_secret' => 'foo_s3cret',
                ],
            ]);
            
            $result = $this->factory->__invoke($container);
            expect($result)->toBeAnInstanceOf(ClientAuthService::class);
        });
    });
});
