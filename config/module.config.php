<?php

namespace ApigilityConsumer;

use ApigilityConsumer\Service\ClientAuthService;
use ApigilityConsumer\Service\ClientAuthServiceFactory;
use ApigilityConsumer\Service\ClientService;
use ApigilityConsumer\Service\ClientServiceFactory;
return [
    'service_manager' => [
        'factories' => [
            ClientAuthService::class => ClientAuthServiceFactory::class,
            ClientService::class => ClientServiceFactory::class,
        ],
    ],
];
