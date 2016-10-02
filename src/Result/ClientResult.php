<?php

namespace ApigilityConsumer\Result;

use ApigilityConsumer\Error\SpecialErrorMessage;
use RuntimeException;
use Zend\Json\Json;

/**
 * An 'entity' as value object that returned in \ApigilityConsumer\Service\ClientService::getClientResult().
 *
 */
class ClientResult implements ResultInterface
{
    /** @var  bool */
    public $success;

    /** @var  array */
    public $data;
    
    /**
     * use static modifier on purpose, to make it usable when assign
     * ClientResult::$messages from outside (eg: on ApigilityConsumer\Service\ClientService::getClientResult()),
     * and it brought to all instance.
     *
     * @var array
     */
    public static $messages = [];

    /**
     * Apply result with return its class as value object
     * when succeed, it will return static::fromSucceed()
     * when failure, it will return static::fromFailure().
     *
     * if decode failed, it will return static::fromFailure() with "Service unavailable" error message
     *
     * There is a condition when the STATIC $messages already setted up via ClientResult::$messages assignment
     * in \ApigilityConsumer\Service\ClientService::getClientResult(), then it will set 'validation_messages' and return failure.
     * There is another condition when 'validation_messages' key is part of result, or result has key ['response']['responseCode']
     * and its value !== '000', it will failure as well... Otherwise, it will return succeed.
     *
     * @param string $result
     *
     * @return self
     */
    public static function applyResult($result)
    {
        if (!empty(self::$messages)) {
            $resultArray['validation_messages'] = self::$messages;

            return static::fromFailure($resultArray);
        }

        try {
            // for handle some characters like "\\" in string
            Json::$useBuiltinEncoderDecoder = true;
            // decode
            $resultArray = Json::decode($result, 1);
        } catch (RuntimeException $e) {
            $resultArray['validation_messages'] = [
                'http' => [
                    SpecialErrorMessage::SERVICE_UNAVAILABLE['code'] => SpecialErrorMessage::SERVICE_UNAVAILABLE['reason'],
                ],
            ];

            return static::fromFailure($resultArray);
        }

        if (isset($resultArray['validation_messages'])) {
            return static::fromFailure($resultArray);
        }

        return static::fromSucceed($resultArray);
    }

    /**
     * A success result, with 'success' property = true
     * For bulk process, there are may happen when not all rows are succeed, they may have error messages
     * that setted via 'failed_*' key.
     *
     * @param array $result
     *
     * @return self
     */
    private static function fromSucceed(array $result)
    {
        $self = new self();
        $self->success = true;
        $self->data    = $result;
        
        return $self;
    }

    /**
     * A failure result process, return self with success = false and its validation_messages when exists.
     *
     * @param array $result
     *
     * @return self
     */
    private static function fromFailure(array $result)
    {
        $self = new self();
        $self->success = false;

        if (isset($result['validation_messages']['http'])) {
            $self::$messages = (isset($result['validation_messages']))
                ? $result['validation_messages']
                : [];
        } else {
            $self::$messages = (isset($result['validation_messages']))
                ? ['validation_messages' => $result['validation_messages']]
                : [];
        }

        return $self;
    }
}