<?php

namespace ApigilityConsumer\Result;

use ApigilityConsumer\Error\SpecialErrorMessage;
use RuntimeException;
use Zend\Json\Json;

class ClientAuthResult implements ResultInterface
{
    /** @var  bool */
    public $success;

    /** @var  array|null */
    public $data;

    /**
     * use static modifier on purpose, to make it usable when assign
     * ClientAuthResult::$messages from outside (eg: on ApigilityConsumer\Service\ClientAuthService::getClientResult()),
     * and it brought to all instance.
     *
     * @var array
     */
    public static $messages = [];

    // avoid class instantiation
    private function __construct()
    {
    }

    /**
     * Apply result with return its class as value object
     * when succeed, it will return static::fromSucceed()
     * when failure, it will return static::fromFailure().
     *
     * if decode failed, it will return static::fromFailure() with "Service unavailable" error message
     *
     * @param string $result
     *
     * @return self
     */
    public static function applyResult($result)
    {
        $resultArray = [];
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

        if (!isset($resultArray['token_type'])) {
            return static::fromFailure($resultArray);
        }

        return static::fromSucceed($resultArray);
    }

    /**
     * A success result, with 'success' property = true.
     *
     * @param array|null $result
     *
     * @return self
     */
    private static function fromSucceed(array $result = null)
    {
        $self = new self();
        $self->success = true;
        $self->data = $result;

        return $self;
    }

    /**
     * A failure result process, return self with success = false
     * with brought messages when exists.
     *
     * @param array|null $result
     *
     * @return self
     */
    private static function fromFailure(array $result = null)
    {
        $self = new self();
        $self->success = false;
        $self::$messages = (isset($result['validation_messages']))
            ? $result['validation_messages']
            : [];

        return $self;
    }
}
