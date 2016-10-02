<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Result\ClientAuthResult;
use ApigilityConsumer\Service\ClientAuthService;
use Kahlan\Plugin\Double;
use Zend\Http\Client;
use Zend\Json\Json;

describe('ClientAuthService', function () {
    
    beforeAll(function () {
        $this->client = Double::instance(['extends' => Client::class]);
        $this->service = new ClientAuthService(
            'http://api.host.url',
            $this->client
        );
    });
    
    describe('->callAPI', function () {
        it('return "ClientAuthService" instance', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'grant_type' => 'password',
                    'username' => 'foo',
                    'password' => '123',
                ],
            ];
            
            allow($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });
    });
});