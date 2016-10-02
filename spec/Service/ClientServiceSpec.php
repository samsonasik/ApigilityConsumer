<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Result\ClientResult;
use ApigilityConsumer\Service\ClientService;
use Kahlan\Plugin\Double;
use Zend\Http\Client;
use Zend\Json\Json;

describe('ClientService', function () {
    
    beforeAll(function () {
        $this->client = Double::instance(['extends' => Client::class]);
        $this->service = new ClientService(
            'http://api.host.url',
            $this->client
        );
    });
    
    describe('->callAPI', function () {
        it('return "ClientAuthService" instance', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'token_type' => 'Bearer',
                'access_token' => 'Acc33sT0ken',
                'form-data' => [
                    'foo' => 'fooValue',
                ],
            ];
            
            allow($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });
    });
});