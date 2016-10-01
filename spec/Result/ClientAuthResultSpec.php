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
        
    });
});