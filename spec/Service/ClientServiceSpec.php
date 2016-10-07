<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Result\ClientResult;
use ApigilityConsumer\Service\ClientService;
use Kahlan\Plugin\Double;
use Zend\Http\Client;
use Zend\Http\Response;
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
        it('return "ClientResult" instance with success = true when status code = 200 and body != ""', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'foo' => 'fooValue',
                ],
            ];
            
            $headers = [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];
            
            $response = Double::instance(['extends' => Response::class]);
            allow($response)->toReceive('getStatusCode')->andReturn(200);
            allow($response)->toReceive('getBody')->andReturn('{}');
                
            allow($this->client)->toReceive('send')->andReturn($response); // Because we want to change the original behavior

            expect($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->client)->toReceive('setHeaders')->with($headers);
            expect($this->client)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->client)->toReceive('setMethod')->with($data['form-request-method']);
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(true);
        });
        
        it('set files data with success if has tmp_name and name key exists', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'token_type' => 'Bearer',
                'access_token' => 'Acc33sT0ken',
                'form-data' => [
                    'foo' => 'fooValue',
                    'files' => [
                        'fileup1' => [
                            'name' => 'fileup1.jpg',
                            'tmp_name' => __DIR__ . '/xyz'
                        ],
                        'fileup2' => [
                            'name' => 'fileup2.jpg',
                            'tmp_name' => __DIR__ . '/xyz'
                        ],
                    ],
                ],
            ];
            
            $headers = [
                'Authorization' => 'Bearer Acc33sT0ken',
                'Accept' => 'application/json',
            ];

            allow($this->client)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));
                
            foreach ($data['form-data']['files'] as $key => $row) {
                expect($this->client)->toReceive('setFileUpload')->with(
                    $row['tmp_name'], $key
                );
            }
            
            $processedData = $data;
            unset($processedData['form-data']['files']);
                        
            expect($this->client)->toReceive('setRawBody')->with(Json::encode($processedData['form-data']));
            expect($this->client)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->client)->toReceive('setHeaders')->with($headers);
            expect($this->client)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->client)->toReceive('setMethod')->with($data['form-request-method']);
            
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });
        
        it('set files data with success if has tmp_name and name key exists and does not has any other data', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'token_type' => 'Bearer',
                'access_token' => 'Acc33sT0ken',
                'form-data' => [
                    'files' => [
                        'fileup1' => [
                            'name' => 'fileup1.jpg',
                            'tmp_name' => __DIR__ . '/xyz'
                        ],
                        'fileup2' => [
                            'name' => 'fileup2.jpg',
                            'tmp_name' => __DIR__ . '/xyz'
                        ],
                    ],
                ],
            ];
            
            $headers = [
                'Authorization' => 'Bearer Acc33sT0ken',
                'Accept' => 'application/json',
            ];

            allow($this->client)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));
                
            foreach ($data['form-data']['files'] as $key => $row) {
                expect($this->client)->toReceive('setFileUpload')->with(
                    $row['tmp_name'], $key
                );
            }
            
            $processedData = $data;
            unset($processedData['form-data']['files']);
                        
            expect($this->client)->toReceive('setRawBody')->with(Json::encode($processedData['form-data']));
            expect($this->client)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->client)->toReceive('setHeaders')->with($headers);
            expect($this->client)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->client)->toReceive('setMethod')->with($data['form-request-method']);
            
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });
        
        it('set files data with success if has tmp_name and name key exists and does not has any other data', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'token_type' => 'Bearer',
                'access_token' => 'Acc33sT0ken',
                'form-data' => [
                    'files' => [
                        'fileup2' => [
                            'name' => 'fileup2.jpg',
                            'tmp_name' => __DIR__ . '/xyzx'
                        ],
                    ],
                ],
            ];
            
            $headers = [
                'Authorization' => 'Bearer Acc33sT0ken',
                'Accept' => 'application/json',
            ];

            allow($this->client)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));
                        
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);
        });
        
        it('set files data with success if doesnot has tmp_name or name key in per-file', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'token_type' => 'Bearer',
                'access_token' => 'Acc33sT0ken',
                'form-data' => [
                    'files' => [
                        'fileup2' => [
                            'name' => 'fileup2.jpg',
                        ],
                    ],
                ],
            ];
            
            $headers = [
                'Authorization' => 'Bearer Acc33sT0ken',
                'Accept' => 'application/json',
            ];

            allow($this->client)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));
                        
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);
        });
        
        it('return "ClientResult" instance with success = false when status code != 200', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'foo' => 'fooValue',
                ],
            ];
            
            $headers = [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];
            
            $response = Double::instance(['extends' => Response::class]);
            allow($response)->toReceive('getStatusCode')->andReturn(400);
                
            allow($this->client)->toReceive('send')->andReturn($response);
            
            expect($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->client)->toReceive('setHeaders')->with($headers);
            expect($this->client)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->client)->toReceive('setMethod')->with($data['form-request-method']);
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);
        });
        
        it('return "ClientResult" instance with success = false when client->send() throw exception', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',
                
                'form-data' => [
                    'foo' => 'fooValue',
                ],
            ];
            
            $headers = [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];
            
            expect($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->client)->toReceive('setHeaders')->with($headers);
            expect($this->client)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->client)->toReceive('setMethod')->with($data['form-request-method']);
            
            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);
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
            
            $headers = [
                'Authorization' => 'Bearer Acc33sT0ken',
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];

            allow($this->client)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));
                
            expect($this->client)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->client)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->client)->toReceive('setHeaders')->with($headers);
            expect($this->client)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->client)->toReceive('setMethod')->with($data['form-request-method']);
            
            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });
        
        
    });
});
