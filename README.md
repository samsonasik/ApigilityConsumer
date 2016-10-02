ApigilityConsumer
=================

Apigility Client module to consume API Services. It contained followed Services:

`ApigilityConsumer\ClientService\ClientService`
-----------------------------------------------

With usage:

```php
use ApigilityConsumer\ClientService\ClientService;

$client = $serviceManager->get(ClientService::class);

$data = [
    'api-route-segment' => '/api',
    'form-request-method' => 'POST',
    
    // fields that will be used as raw json with 'form-data' index
    'form-data' => [
        'foo' => 'fooValue',
    ],
];
$timeout  = 100;
$clientResult = $client->callAPI($data, $timeout);
```

The `$clientResult` will be a `ApigilityConsumer\Result\ClientResult` instance, with this instance, you can do:

```php
if (! $clientResult->success) {
    var_dump($clientResult::$messages);
} else {
    var_dump($clientResult->data);
}
```

# `ApigilityConsumer\ClientService\ClientAuthService`
-----------------------------------------------------

It used for `oauth`, with usage:

```php
use ApigilityConsumer\ClientService\ClientAuthService;

$client = $serviceManager->get(ClientAuthService::class);

$data = [
    'api-route-segment' => '/oauth',
    'form-request-method' => 'POST',

    'form-data' => [
        'grant_type' => 'password',
        'username' => 'foo',
        'password' => '123',
    ],
];
$timeout  = 100;
$clientResult = $client->callAPI($data, $timeout);
```

The `$clientResult` will be a `ApigilityConsumer\Result\ClientAuthResult` instance, with this instance, you can do:

```php
if (! $clientResult->success) {
    var_dump($clientResult::$messages);
} else {
    var_dump($clientResult->data);
}
```

# Installation

- composer!
```
composer require samsonasik/apigility-consumer
```

- copy `vendor/samsonasik/apigility-consumer/config/apigility-consumer.local.php.dist` to `config/autoload/apigility-consumer.local.php` and configure with your api host and oauth settings:

```php
<?php

return [
    'api-host-url' => 'http://api.host.com',
    'oauth' => [
        'grant_type'    => 'password',
        'client_id'     => 'foo',
        'client_secret' => 'foo_s3cret',
    ],
];
```

- Register to config/modules.config.php
```
    return [
        'modules' => [
            'ApiglityConsumer', <-- register here
            'Application'
        ],
    ];
```

