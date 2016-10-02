<?php

namespace ApigilityConsumer\Spec\Result;

use ApigilityConsumer\Error\SpecialErrorMessage;
use ApigilityConsumer\Result\ClientAuthResult;

describe('ClientAuthResult', function () {
    
    beforeAll(function () {
        $this->result = new ClientAuthResult();
    });
 
    describe('::applyResult', function () {
        it('set success = false when self::$messages is not empty', function () {
            ClientAuthResult::$messages = [
                'http' => [
                    SpecialErrorMessage::INVALID_REQUEST['code'] => SpecialErrorMessage::INVALID_REQUEST['reason']
                ],
            ];
            
            $result = $this->result->applyResult('{}');
            expect(false)->toBe($result->success);
        });
        
        it('set success = false when provided json is invalid', function () {
            ClientAuthResult::$messages = [];
            
            $result = $this->result->applyResult('invalid json');
            expect(false)->toBe($result->success);
        });
        
        it('set success = false when login failed', function () {
            ClientAuthResult::$messages = [];

            $response = <<<json
{
    "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
    "title": "invalid_request",
    "status": 400,
    "detail": "The grant type was not specified in the request"
}
json;

            $result = $this->result->applyResult($response);
            expect(false)->toBe($result->success);
        });
        
        it('set success = true when login succeed', function () {
            ClientAuthResult::$messages = [];

            $response = <<<json
            {
              "access_token": "8e4b0e5ddc874a6f1500514ef530dbea3976ae77",
              "expires_in": 3600,
              "token_type": "Bearer",
              "scope": null,
              "refresh_token": "d19b79cd376924409c14ee46e5230617482fb169"
            }
json;

            $result = $this->result->applyResult($response);
            expect(true)->toBe($result->success);
        });

    });
});