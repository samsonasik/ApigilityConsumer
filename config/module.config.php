<?php

namespace ApigilityConsumer;

return [
    'service_manager' => [
        'factories' => [
            Service\ClientAuthService::class => Service\ClientAuthServiceFactory::class,
            Service\ClientService::class => Service\ClientServiceFactory::class,
        ],
    ],
];
