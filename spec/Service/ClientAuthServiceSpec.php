<?php

namespace ApigilityConsumer\Spec\Service;

use ApigilityConsumer\Result\ClientAuthResult;
use ApigilityConsumer\Service\ClientAuthService;
use InvalidArgumentException;
use Kahlan\Plugin\Double;
use ReflectionProperty;
use Zend\Http\Client;
use Zend\Http\Response;
use Zend\Json\Json;

describe('ClientAuthService', function () {
    beforeAll(function () {
        $this->httpClient = Double::instance(['extends' => Client::class]);
        $this->service = new ClientAuthService(
            'http://api.host.url',
            $this->httpClient,
            [
                'grant_type'    => 'password',
                'client_id'     => 'foo',
                'client_secret' => 'foo_s3cret',
            ]
        );
    });

    describe('->resetClient()', function () {

        it('reset $client property back to null', function () {

            $service = new ClientAuthService(
                'http://api.host.url',
                $this->httpClient,
                [
                    'grant_type'    => 'password',
                    'client_id'     => 'foo',
                    'client_secret' => 'foo_s3cret',

                    'clients' => [
                        'bar' => [
                            'grant_type' => 'password',
                            'client_secret' => 'bar_s3cret',
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
        it('define grant_type = client_credentials will not set username form-data', function () {
            $httpClient = Double::instance(['extends' => Client::class]);
            $service = new ClientAuthService(
                'http://api.host.url',
                $httpClient,
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => 'foo',
                    'client_secret' => 'foo_s3cret',
                ]
            );

            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',
            ];

            allow($httpClient)->toReceive('setRawBody')->with(Json::encode(
                [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => 'foo',
                    'client_secret' => 'foo_s3cret',
                ]
            ));

            $result = $service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });

        it('throws InvalidArgumentException when withClient() called and client is not defined in the config', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',

                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];

            $closure = function () use ($data) {
                $this->service
                            ->withClient('not_registered_client')
                            ->callAPI($data, 100);
            };
            expect($closure)->toThrow(new InvalidArgumentException('client selected not found in the "clients" config'));

        });

        it('return "ClientAuthResult" instance', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',

                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];

            allow($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));

            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });

        it('return "ClientAuthResult" instance on withClient() with registered client', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',

                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];

            $service = new ClientAuthService(
                'http://api.host.url',
                $this->httpClient,
                [
                    'grant_type'    => 'password',
                    'client_id'     => 'foo',
                    'client_secret' => 'foo_s3cret',

                    'clients' => [
                        'bar' => [
                            'grant_type' => 'password',
                            'client_secret' => 'bar_s3cret',
                        ],
                    ],

                ]
            );

            allow($this->httpClient)->toReceive('setRawBody')->with(Json::encode($data['form-data']));

            $service->withClient('bar');
            $result = $service->callAPI($data);
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

            $dataTobeSent = [
                'grant_type' => 'password',
                'client_id' =>  'foo',
                'client_secret' => 'foo_s3cret',
                'username'    => 'foo',
                'password'     => 'foo',
            ];

            $headers = [
                'Accept' => 'application/json',
                'Content-type' => 'application/json'
            ];

            allow($this->httpClient)->toReceive('send')->andReturn(Double::instance(['extends' => Response::class]));

            expect($this->httpClient)->toReceive('setRawBody')->with(Json::encode($dataTobeSent));
            expect($this->httpClient)->toReceive('setOptions')->with(['timeout' => 100]);
            expect($this->httpClient)->toReceive('setHeaders')->with($headers);
            expect($this->httpClient)->toReceive('setUri')->with('http://api.host.url/oauth');
            expect($this->httpClient)->toReceive('setMethod')->with($data['form-request-method']);

            $result = $this->service->callAPI($data, 100);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
        });

        it('return invalid request when invalid data provided', function () {
            $data = [
                'api-route-segment' => '/oauth',
                'form-request-method' => 'POST',

                'form-data' => [
                    'username'    => 'foo',
                    'password'     => 'foo',
                ],
            ];

            $response = Double::instance(['extends' => Response::class]);
            allow($response)->toReceive('getStatusCode')->andReturn(400);
            allow($response)->toReceive('getBody')->andReturn(<<<json
{
  "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
  "title": "invalid_client",
  "status": 400,
  "detail": "The client credentials are invalid"
}
json
            );

            allow($this->httpClient)->toReceive('send')->andReturn($response);

            $result = $this->service->callAPI($data);
            expect($result)->toBeAnInstanceOf(ClientAuthResult::class);
            expect($result->success)->toBe(false);
        });
    });
});
