<?php

declare(strict_types=1);

namespace ApigilityConsumer;

class ConfigProvider
{
    public function __invoke(): array
    {
        $config = include __DIR__ . '/../config/module.config.php';
        return [
            'dependencies' => $config['service_manager'],
        ];
    }
}
