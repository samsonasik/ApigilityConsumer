<?php

declare(strict_types=1);

namespace ApigilityConsumer\Result;

interface ResultInterface
{
    public static function applyResult(string $result): self;
}
