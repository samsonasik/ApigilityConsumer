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
        it('return "ClientResult" instance', function () {
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
        
        it('define timeout parameter will set timeout for http call', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'token_type' => 'Bearer',
                'access_token' => 'Acc33sT0ken',
                'form-data' => [
                    'foo' => 'fooValue',
                ],
            ];
            
            allow($this->client)->toReceive('setOptions')->with(['timeout' => 100]);
            
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });
    });
});