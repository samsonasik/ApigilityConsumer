<?php

declare(strict_types=1);

namespace ApigilityConsumer;

class Module
{
    /**
     * @return mixed[]
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
