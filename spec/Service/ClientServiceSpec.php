<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Result\ClientResult;
use ApigilityConsumer\Service\ClientService;
use InvalidArgumentException;
use Kahlan\Plugin\Double;
use ReflectionProperty;
use RuntimeException;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Http\Client;
use Laminas\Http\Response;
use Laminas\Json\Json;

describe('ClientService', function () {
    beforeAll(function (): void {
        $this->httpClient = Double::instance(['extends' => Client::class]);
        $this->service = new ClientService(
            'http://api.host.url',
            $this->httpClient,
            [
                Client::AUTH_BASIC => [
                    'username' => 'foo',
                    'password' => 'foo_s3cret'
                ],
                Client::AUTH_DIGEST => [
                    'username' => 'foo',
                    'password' => 'foo_s3cret'
                ],
            ]
        );
    });

    describe('->resetHttpAuthType()', function (): void {

        it('reset $authType property back to null', function (): void {

            $service = $this->service->withHttpAuthType(Client::AUTH_BASIC);
            $r = new ReflectionProperty($service, 'authType');
            $r->setAccessible(true);

            expect($r->getValue($service))->toBe(Client::AUTH_BASIC);

            $service->resetHttpAuthType();
            expect($r->getValue($service))->toBe(null);

        });

    });

    describe('->resetClient()', function (): void {

        it('reset $client property back to null', function (): void {

            $service = new ClientService(
                'http://api.host.url',
                $this->httpClient,
                [
                    Client::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    Client::AUTH_DIGEST => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],

                    'clients' => [
                        'bar' => [ // bar is client_id
                            Client::AUTH_BASIC => [
                                'username' => 'bar',
                                'password' => 'bar_s3cret'
                            ],

                            Client::AUTH_DIGEST => [
                                'username' => 'bar',
                                'password' => 'bar_s3cret'
                            ],
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
        it('return "ClientResult" instance with success = true when status code = 200 and body != ""', function (): void {

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

        it('return "ClientResult" instance with success = false when status code = 200 and body = ""', function (): void {

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

        it('set files data with success if has tmp_name and name key exists', function (): void {

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

        it('set files data with success if has tmp_name and name key exists and does not has any other data', function (): void {

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

        it('set files data with not success if has tmp_name and name key not exists and does not has any other data', function (): void {

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

        it('set files data with success if doesnot has tmp_name or name key in per-file', function (): void {

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

        it('return "ClientResult" instance with success = false when status code != 200', function (): void {

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
            allow($this->httpClient)->toReceive('send')->andRun(function () {
                throw new RuntimeException();
            });

            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);

        });

        it('define timeout parameter will set timeout for http call', function (): void {

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

        it('throws InvalidArgumentException when withHttpAuthType() called and authType is not "basic" or "digest"', function (): void {

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

            $closure = function () use ($data): void {
                $this->service
                            ->withHttpAuthType('not_basic_nor_digest')
                            ->callAPI($data, 100);
            };
            expect($closure)->toThrow(new InvalidArgumentException('authType selected should be a basic or digest'));

        });

        it('call client->setAuth() when withHttpAuthType() and withClient() called and exists in configuration', function (): void {

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

            expect($this->httpClient)->toReceive('setAuth')->with('bar', 'bar_s3cret', Client::AUTH_BASIC);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $service = new ClientService(
                'http://api.host.url',
                $this->httpClient,
                [
                    Client::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    Client::AUTH_DIGEST => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],

                    'clients' => [
                        'bar' => [ // bar is client_id
                            Client::AUTH_BASIC => [
                                'username' => 'bar',
                                'password' => 'bar_s3cret'
                            ],

                            Client::AUTH_DIGEST => [
                                'username' => 'bar',
                                'password' => 'bar_s3cret'
                            ],
                        ],
                    ],
                ]
            );

            $clientResult = $service
                            ->withHttpAuthType(Client::AUTH_BASIC)
                            ->withClient('bar')
                            ->callAPI($data, 100);
            expect($clientResult)->toBeAnInstanceOf(ClientResult::class);

        });

        it('call client->setAuth() when withHttpAuthType() called and exists in configuration', function (): void {

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

            expect($this->httpClient)->toReceive('setAuth')->with('foo', 'foo_s3cret', Client::AUTH_BASIC);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service
                            ->withHttpAuthType(Client::AUTH_BASIC)
                            ->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);

        });

        it('call client->setAuth() when withHttpAuthType() called and exists in $data parameter', function (): void {

            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',

                'form-data' => [
                    'foo' => 'fooValue',
                ],

                'auth' => [
                    Client::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    Client::AUTH_DIGEST => [
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

            expect($this->httpClient)->toReceive('setAuth')->with('foo', 'foo_s3cret', Client::AUTH_BASIC);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service
                            ->withHttpAuthType(Client::AUTH_BASIC)
                            ->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);

        });

        it('call client->setAuth() with setAdapter(Curl:class) when withHttpAuthType() called for AUTH_DIGEST', function (): void {

            $data = [
                'api-route-segment' => '/api',
                'form-request-method' => 'POST',

                'form-data' => [
                    'foo' => 'fooValue',
                ],

                'auth' => [
                    Client::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                    Client::AUTH_DIGEST => [
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
            expect($this->httpClient)->toReceive('setAuth')->with('foo', 'foo_s3cret', Client::AUTH_DIGEST);
            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/api');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service
                            ->withHttpAuthType(Client::AUTH_DIGEST)
                            ->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientResult::class);

        });


        it('throws InvalidArgumentException when withClient() called and client is not defined in the config', function (): void {

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

            $closure = function () use ($data): void {
                $this->service
                            ->withClient('not_registered_client')
                            ->callAPI($data, 100);
            };
            expect($closure)->toThrow(new InvalidArgumentException('client selected not found in the "clients" config'));

        });

        it('set not success on Http Client send got RuntimeException', function () {

            $data = [
                'api-route-segment'   => '/api',
                'form-request-method' => 'POST',
                'form-data'           => [],
            ];

            $headers = [
                'Accept'       => 'application/json',
                'Content-type' => 'application/json'
            ];

            allow($this->httpClient)->toReceive('send')->andRun(function () {
                throw new RuntimeException();
            });

            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientResult::class);
            expect($result->success)->toBe(false);

        });

    });

});
