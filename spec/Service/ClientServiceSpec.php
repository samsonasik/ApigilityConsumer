<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Result\ClientResult;
use ApigilityConsumer\Service\ClientService;
use InvalidArgumentException;
use Kahlan\Plugin\Double;
use ReflectionProperty;
use Zend\Http\Client\Adapter\Curl;
use Zend\Http\Client as HttpClient;
use Zend\Http\Response;
use Zend\Json\Json;

describe('ClientService', function () {
    beforeAll(function () {
        $this->httpClient = Double::instance(['extends' => HttpClient::class]);
        $this->service = new ClientService(
            'http://api.host.url',
            $this->httpClient,
            [
                HttpClient::AUTH_BASIC => [
                    'username' => 'foo',
                    'password' => 'foo_s3cret'
                ],
                HttpClient::AUTH_DIGEST => [
                    'username' => 'foo',
                    'password' => 'foo_s3cret'
                ],
            ]
        );
    });

    describe('->resetHttpAuthType()', function () {

        it('reset $authType property back to null', function () {

            $service = $this->service->withHttpAuthType(HttpClient::AUTH_BASIC);
            $r = new ReflectionProperty($service, 'authType');
            $r->setAccessible(true);
            expect($r->getValue($service))->toBe(HttpClient::AUTH_BASIC);

            $service->resetHttpAuthType();
            expect($r->getValue($service))->toBe(null);

        });

    });

    describe('->resetClient()', function () {

        it('reset $client property back to null', function () {

            $service = new ClientService(
                'http://api.host.url',
                $this->httpClient,
                [
                    HttpClient::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    HttpClient::AUTH_DIGEST => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],

                    'clients' => [
                        'bar' => [
                            'username' => 'bar',
                            'password' => 'bar_s3cret'
                        ],
                    ],
                ]
            );

            $service = $service->withClient('bar');
            $r = new ReflectionProperty($service, 'client');
            $r->setAccessible(true);
            expect($r->getValue($service))->toBe('bar');

            $service->resetClient();
            expect($r->getValue($service))->toBe(null);

        });

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

            allow($this->httpClient)->toReceive('send')->andReturn($response); // Because we want to change the original behavior

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(true);
        });

        it('return "ClientResult" instance with success = false when status code = 200 and body = ""', function () {
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
            allow($response)->toReceive('getBody')->andReturn('');

            allow($this->httpClient)->toReceive('send')->andReturn($response); // Because we want to change the original behavior

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);
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

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            foreach ($data['form-data']['files'] as $key => $row) {
                expect($this->httpClient)->toReceive('setFileUpload')->with(
                    $row['tmp_name'], $key
                );
            }

            $processedData = $data;
            unset($processedData['form-data']['files']);

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($processedData['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

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

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            foreach ($data['form-data']['files'] as $key => $row) {
                expect($this->httpClient)->toReceive('setFileUpload')->with(
                    $row['tmp_name'], $key
                );
            }

            $processedData = $data;
            unset($processedData['form-data']['files']);

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($processedData['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

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

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

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

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

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

            allow($this->httpClient)->toReceive('send')->andReturn($response);

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

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

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

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

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });

        it('throws InvalidArgumentException when withHttpAuthType() called and authType is not "basic" or "digest"', function () {
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

            $closure = function () use ($data) {
                $this->service
                            ->withHttpAuthType('not_basic_nor_digest')
                            ->callAPI($data, 100);
            };
            expect($closure)->toThrow(new InvalidArgumentException('authType selected should be a basic or digest'));

        });

        it('call client->setAuth() when withHttpAuthType() called and exists in configuration', function () {
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

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            expect($this->httpClient)->toReceive('setAuth')->with('foo', 'foo_s3cret', HttpClient::AUTH_BASIC);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service
                            ->withHttpAuthType(HttpClient::AUTH_BASIC)
                            ->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });

        it('call client->setAuth() when withHttpAuthType() called and exists in $data parameter', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',

                'form-data' => [
                    'foo' => 'fooValue',
                ],

                'auth' => [
                    HttpClient::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    HttpClient::AUTH_DIGEST => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                ],
            ];

            $headers = [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            expect($this->httpClient)->toReceive('setAuth')->with('foo', 'foo_s3cret', HttpClient::AUTH_BASIC);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service
                            ->withHttpAuthType(HttpClient::AUTH_BASIC)
                            ->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });

        it('call client->setAuth() with setAdapter(Curl:class) when withHttpAuthType() called for AUTH_DIGEST', function () {
            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',

                'form-data' => [
                    'foo' => 'fooValue',
                ],

                'auth' => [
                    HttpClient::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    HttpClient::AUTH_DIGEST => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                ],
            ];

            $headers = [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            expect($this->httpClient)->toReceive('setAdapter')->with(Curl::class);
            expect($this->httpClient)->toReceive('setAuth')->with('foo', 'foo_s3cret', HttpClient::AUTH_DIGEST);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service
                            ->withHttpAuthType(HttpClient::AUTH_DIGEST)
                            ->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
        });


        it('throws InvalidArgumentException when withClient() called and client is not defined in the config', function () {
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

            $closure = function () use ($data) {
                $this->service
                            ->withClient('not_registered_client')
                            ->callAPI($data, 100);
            };
            expect($closure)->toThrow(new InvalidArgumentException('client selected not found in the "clients" config'));

        });

    });
});
