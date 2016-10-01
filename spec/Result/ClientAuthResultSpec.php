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
            $this->result::$messages = [
                'http' => [
                    SpecialErrorMessage::INVALID_REQUEST['code'] => SpecialErrorMessage::INVALID_REQUEST['reason']
                ],
            ];
            
            $result = $this->result->applyResult('{}');
            expect(false)->toBe($result->success);
        });
        
        it('set success = false when provided json is invalid', function () {
            $this->result::$messages = [];
            
            $result = $this->result->applyResult('invalid json');
            expect(false)->toBe($result->success);
        });
        
        it('set success = false when login failed', function () {
            $this->result::$messages = [];

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

    });
});