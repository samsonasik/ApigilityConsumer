<?php

namespace ApigilityConsumer\Result;

interface ResultInterface
{
    /**
     * @param string $result
     *
     * @return self
     */
    public static function applyResult($result);
}
