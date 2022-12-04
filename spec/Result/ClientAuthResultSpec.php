<?php

namespace ApigilityConsumer\Spec\Result;

use ApigilityConsumer\Result\ClientAuthResult;
use Error;
use ReflectionMethod;

describe('ClientAuthResult', function (): void {

    describe('->__construct()', function (): void {

        it('a private constructor', function (): void {

            $r = new ReflectionMethod(ClientAuthResult::class, '__construct');
            expect($r->isPrivate())->toBe(true);

        });

        it('cannot create instance via new ClientAuthResult()', function (): void {

            skipIf(PHP_MAJOR_VERSION < 7);

            try {
                new ClientAuthResult();
            } catch (Error $error) {
                expect($error->getMessage())
                    ->toBe('Call to private ' . ClientAuthResult::class . '::__construct() from scope Kahlan\Cli\Kahlan');
            }

        });

    });

    describe('::applyResult', function (): void {

        it('set success = false when self::$messages is not empty', function (): void {

            ClientAuthResult::$messages = [
                'http' => [
                    204 => 'No Content'
                ],
            ];

            $clientAuthResult = ClientAuthResult::applyResult('{}');
            expect(false)->toBe($clientAuthResult->success);

        });

        it('set success = false when provided json is invalid', function (): void {

            ClientAuthResult::$messages = [];

            $clientAuthResult = ClientAuthResult::applyResult('invalid json');
            expect(false)->toBe($clientAuthResult->success);

        });

        it('set success = false when login failed', function (): void {

            ClientAuthResult::$messages = [];
            $jsonData = ['type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html', 'title' => 'invalid_request', 'status' => 400, 'detail' => 'The grant type was not specified in the request'];

            $response = json_encode($jsonData, JSON_THROW_ON_ERROR);

            $clientAuthResult = ClientAuthResult::applyResult($response);
            expect(false)->toBe($clientAuthResult->success);

        });

        it('set success = true when login succeed', function (): void {

            ClientAuthResult::$messages = [];
            $jsonData = ['access_token' => '8e4b0e5ddc874a6f1500514ef530dbea3976ae77', 'expires_in' => 3600, 'token_type' => 'Bearer', 'scope' => null, 'refresh_token' => 'd19b79cd376924409c14ee46e5230617482fb169'];

            $response = json_encode($jsonData, JSON_THROW_ON_ERROR);

            $clientAuthResult = ClientAuthResult::applyResult($response);
            expect(true)->toBe($clientAuthResult->success);

        });

    });

});
