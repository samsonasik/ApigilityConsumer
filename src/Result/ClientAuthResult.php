<?php

declare(strict_types=1);

namespace ApigilityConsumer\Result;

use ApigilityConsumer\Error\SpecialErrorMessage as ErrorMessage;
use Laminas\Json\Json;
use RuntimeException;

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

    /**
     * avoid class instantiation
     */
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
     * @return self
     */
    public static function applyResult(string $result): ResultInterface
    {
        $resultArray = [];
        if (! empty(self::$messages)) {
            $resultArray['validation_messages'] = self::$messages;

            return static::fromFailure($resultArray);
        }

        try {
            // for handle some characters like "\\" in string
            Json::$useBuiltinEncoderDecoder = true;
            // decode
            $resultArray = Json::decode($result, Json::TYPE_ARRAY);
        } catch (RuntimeException $e) {
            $resultArray['validation_messages'] = [
                'http' => [
                    ErrorMessage::SERVICE_UNAVAILABLE['code'] => ErrorMessage::SERVICE_UNAVAILABLE['reason'],
                ],
            ];

            return static::fromFailure($resultArray);
        }

        return ! isset($resultArray['token_type'])
            ? static::fromFailure($resultArray)
            : static::fromSucceed($resultArray);
    }

    /**
     * A success result, with 'success' property = true.
     */
    private static function fromSucceed(?array $result): self
    {
        $self          = new self();
        $self->success = true;
        $self->data    = $result;

        return $self;
    }

    /**
     * A failure result process, return self with success = false
     * with brought messages when exists.
     */
    private static function fromFailure(?array $result): self
    {
        $self            = new self();
        $self->success   = false;
        $self::$messages = $result['validation_messages'] ?? [];

        return $self;
    }
}
