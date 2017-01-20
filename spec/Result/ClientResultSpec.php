<?php

namespace ApigilityConsumer\Spec\Result;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientResult;

describe('ClientResult', function () {

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
