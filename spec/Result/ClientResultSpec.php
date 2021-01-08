<?php

namespace ApigilityConsumer\Spec\Result;

use ApigilityConsumer\Result\ClientResult;
use Error;
use ReflectionMethod;

describe('ClientResult', function () {

    describe('->__construct()', function () {

        it('a private constructor', function () {

            $r = new ReflectionMethod(ClientResult::class, '__construct');
            expect($r->isPrivate())->toBe(true);

        });

        it('cannot create instance via new ClientResult()', function () {

            try {
                new ClientResult();
            } catch (Error $e) {
                expect($e->getMessage())->toBe("Call to private ApigilityConsumer\\Result\\ClientResult::__construct() from scope Kahlan\\Cli\\Kahlan");
            }

        });

    });

    describe('::applyResult', function () {

        it('set success = false when self::$messages is not empty', function () {

            ClientResult::$messages = [
                'http' => [
                    204 => 'No Content'
                ],
            ];

            $result = ClientResult::applyResult('{}');
            expect(false)->toBe($result->success);

        });

        it('set success = false when provided json is invalid', function () {

            ClientResult::$messages = [];

            $result = ClientResult::applyResult('invalid json');
            expect(false)->toBe($result->success);

        });

        it('set success = false when validation_messages is not empty', function () {

            ClientResult::$messages = [];

            $response = <<<json
{
    "validation_messages": {
        "foo": {
            "regexNotMatch": "The input does not match against pattern '/^[a-zA-Z0-9 .\\\\-]+$/'"
        }
    },
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "title": "Unprocessable Entity",
    "status": 422,
    "detail": "Failed Validation"
}
json;

            $result = ClientResult::applyResult($response);
            expect(false)->toBe($result->success);

        });

        it('set success = true when validation_messages is empty and $messages is empty too', function () {

            ClientResult::$messages = [];

            $response = <<<json
{
    "data": {

    }
}
json;

            $result = ClientResult::applyResult($response);
            expect(true)->toBe($result->success);

        });

    });

});
