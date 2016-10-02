ApigilityConsumer
=================

[![Build Status](https://travis-ci.org/samsonasik/ApigilityConsumer.svg?branch=master)](https://travis-ci.org/samsonasik/ApigilityConsumer)

Apigility Client module to consume API Services. 

Installation
------------

```
composer require samsonasik/apigility-consumer
```

After installed, copy `vendor/samsonasik/apigility-consumer/config/apigility-consumer.local.php.dist` to `config/autoload/apigility-consumer.local.php` and configure with your api host and oauth settings:

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

Then, Enable it :

```
    return [
        'modules' => [
            'ApigilityConsumer', <-- register here
            'Application'
        ],
    ];
```


Services
--------

- *`ApigilityConsumer\Service\ClientService`*

With usage:

```php
use ApigilityConsumer\Service\ClientService;

$client = $serviceManager->get(ClientService::class);

$data = [
    'api-route-segment' => '/api',
    'form-request-method' => 'POST',
    
    'form-data' => [
        // fields that will be used as raw json to be sent
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

- *`ApigilityConsumer\Service\ClientAuthService`*

It used for `oauth`, with usage:

```php
use ApigilityConsumer\Service\ClientAuthService;

$client = $serviceManager->get(ClientAuthService::class);

$data = [
    'api-route-segment' => '/oauth',
    'form-request-method' => 'POST',

    'form-data' => [
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


