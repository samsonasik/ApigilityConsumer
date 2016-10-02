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
            $this->client,
            [
                'grant_type'    => 'password',
                'client_id'     => 'foo',
                'client_secret' => 'foo_s3cret',
            ]
        );
    });
    
    describe('->callAPI', function () {
        it('return "ClientAuthResult" instance', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];
            
            allow($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });
        
        it('define timeout parameter will set timeout for http call', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];
            
            allow($this->client)->toReceive('setOptions')->with(['timeout' => 100]);
            
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });
        
        it('return invalid request when invalid data provided', function() {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });
    });
    
});