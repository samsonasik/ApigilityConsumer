<?php

declare(strict_types=1);

namespace ApigilityConsumer\Result;

use ApigilityConsumer\Error\SpecialErrorMessage;
use Laminas\Json\Json;
use RuntimeException;

class ClientAuthResult implements ResultInterface
{
    public ?bool $success = null;

    public ?array $data = null;

    /**
     * use static modifier on purpose, to make it usable when assign
     * ClientAuthResult::$messages from outside (eg: on ApigilityConsumer\Service\ClientAuthService::getClientResult()),
     * and it brought to all instance.
     */
    public static array $messages = [];

    /**
     * avoid class instantiation
     */
    private function __construct()
    {
    }

    /**
     * Apply result with return its class as value object
     * when succeed, it will return self::fromSucceed()
     * when failure, it will return self::fromFailure().
     *
     * if decode failed, it will return self::fromFailure() with "Service unavailable" error message
     *
     * @return self
     */
    public static function applyResult(string $result): ResultInterface
    {
        $resultArray = [];
        if (self::$messages !== []) {
            $resultArray['validation_messages'] = self::$messages;

            return self::fromFailure($resultArray);
        }

        try {
            // for handle some characters like "\\" in string
            Json::$useBuiltinEncoderDecoder = true;
            // decode
            $resultArray = Json::decode($result, Json::TYPE_ARRAY);
        } catch (RuntimeException) {
            $resultArray['validation_messages'] = [
                'http' => [
                    SpecialErrorMessage::SERVICE_UNAVAILABLE['code']
                        => SpecialErrorMessage::SERVICE_UNAVAILABLE['reason'],
                ],
            ];

            return self::fromFailure($resultArray);
        }

        return isset($resultArray['token_type'])
            ? self::fromSucceed($resultArray)
            : self::fromFailure($resultArray);
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
