<?php

declare(strict_types=1);

namespace ApigilityConsumer\Service;

use ApigilityConsumer\Result\ResultInterface;

interface ClientApiInterface
{
    /**
     * Process Call API.
     *
     * @param array    $data
     * @param int|null $timeout
     *
     * @return ResultInterface
     */
    public function callAPI(array $data, int $timeout = null) : ResultInterface;
}
