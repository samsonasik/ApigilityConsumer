<?php

namespace ApigilityConsumer\Spec\Result;

use ApigilityConsumer\Result\ClientResult;
use Error;
use ReflectionMethod;

describe('ClientResult', function (): void {

    describe('->__construct()', function (): void {

        it('a private constructor', function (): void {

            $r = new ReflectionMethod(ClientResult::class, '__construct');
            expect($r->isPrivate())->toBe(true);

        });

        it('cannot create instance via new ClientResult()', function (): void {

            skipIf(PHP_MAJOR_VERSION < 7);

            try {
                new ClientResult();
            } catch (Error $error) {
                expect($error->getMessage())->toBe('Call to private ' . ClientResult::class . '::__construct() from scope Kahlan\Cli\Kahlan');
            }

        });

    });

    describe('::applyResult', function (): void {

        it('set success = false when self::$messages is not empty', function (): void {

            ClientResult::$messages = [
                'http' => [
                    204 => 'No Content'
                ],
            ];

            $clientResult = ClientResult::applyResult('{}');
            expect(false)->toBe($clientResult->success);

        });

        it('set success = false when provided json is invalid', function (): void {

            ClientResult::$messages = [];

            $clientResult = ClientResult::applyResult('invalid json');
            expect(false)->toBe($clientResult->success);

        });

        it('set success = false when validation_messages is not empty', function (): void {

            ClientResult::$messages = [];
            $jsonData = ['validation_messages' => ['foo' => ['regexNotMatch' => 'The input does not match against pattern \'/^[a-zA-Z0-9 .\-]+$/\'']], 'type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html', 'title' => 'Unprocessable Entity', 'status' => 422, 'detail' => 'Failed Validation'];

            $response = json_encode($jsonData, JSON_THROW_ON_ERROR);

            $clientResult = ClientResult::applyResult($response);
            expect(false)->toBe($clientResult->success);

        });

        it('set success = true when validation_messages is empty and $messages is empty too', function (): void {

            ClientResult::$messages = [];
            $jsonData = ['data' => []];

            $response = json_encode($jsonData, JSON_THROW_ON_ERROR);

            $clientResult = ClientResult::applyResult($response);
            expect(true)->toBe($clientResult->success);

        });

    });

});
