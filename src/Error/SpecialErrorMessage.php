<?php

declare(strict_types=1);

namespace ApigilityConsumer\Error;

/**
 * This class is used for apply special error
 * - RESOURCE_NOT_AVAILABLE not available is for server not found/down
 * - INVALID_REQUEST_FILE for invalid files data
 * - SERVICE_UNAVAILABLE  means there is error in server when decode data.
 */
final class SpecialErrorMessage
{
    public const RESOURCE_NOT_AVAILABLE = [
        'code'   => 410,
        'reason' => 'API Call failed, The target resource %s is no longer available, '
            . 'please check your ApigilityConsumer config, '
            . 'and/or ask API service administrator whether the API server is down.',
    ];

    public const INVALID_REQUEST_FILE = [
        'code'   => 400,
        'reason' => 'Invalid files data, please make sure you have "tmp_name" and "name" key',
    ];

    public const SERVICE_UNAVAILABLE = [
        'code'   => 503,
        'reason' => 'Service Unavailable, please contact API service administrator.',
    ];
}
