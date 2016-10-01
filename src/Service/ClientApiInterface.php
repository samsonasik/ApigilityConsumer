<?php

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
    public function callAPI(array $data, $timeout = null);
}
