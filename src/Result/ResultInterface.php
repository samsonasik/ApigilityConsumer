<?php

declare(strict_types=1);

namespace ApigilityConsumer\Result;

interface ResultInterface
{
    /**
     * @param string $result
     *
     * @return self
     */
    public static function applyResult(string $result) : ResultInterface;
}
