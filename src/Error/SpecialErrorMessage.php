<?php

namespace ApigilityConsumer\Error;

/**
 * This class is used for apply special error
 * - RESOURCE_NOT_AVAILABLE not available is for server not found/down
 * - INVALID_REQUEST request means data passed can't be processed
 * - SERVICE_UNAVAILABLE  means there is error in server when decode data.
 *
 */
final class SpecialErrorMessage
{
    const RESOURCE_NOT_AVAILABLE = [
        'code' => 410,
        'reason' => 'API Call failed, The target resource %s is no longer available, please check your ApigilityConsumer config, and/or ask API maintenance whether the API server is down.',
    ];

    const INVALID_REQUEST = [
        'code' => 400,
        'reason' => 'Data decoding error.',
    ];

    const SERVICE_UNAVAILABLE = [
        'code' => 503,
        'reason' => 'Service Unavailable, please contact API service administrator.',
    ];
}
