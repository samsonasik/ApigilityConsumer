ApigilityConsumer
=================

[![Build Status](https://travis-ci.org/samsonasik/ApigilityConsumer.svg?branch=master)](https://travis-ci.org/samsonasik/ApigilityConsumer)
[![Coverage Status](https://coveralls.io/repos/github/samsonasik/ApigilityConsumer/badge.svg?branch=master)](https://coveralls.io/github/samsonasik/ApigilityConsumer?branch=master)
[![Downloads](https://img.shields.io/packagist/dt/samsonasik/apigility-consumer.svg?style=flat-square)](https://packagist.org/packages/samsonasik/apigility-consumer)

ZF2 and ZF3 Apigility Client module to consume API Services. 

Installation
------------

Installation of this module uses [getcomposer.org](composer).

```sh
composer require samsonasik/apigility-consumer
```

For its configuration, copy `vendor/samsonasik/apigility-consumer/config/apigility-consumer.local.php.dist` to `config/autoload/apigility-consumer.local.php` and configure with your api host and oauth settings:

```php

return [
    'api-host-url' => 'http://api.host.com',
    'oauth' => [
        'grant_type'    => 'password', // or client_credentials
        'client_id'     => 'foo',
        'client_secret' => 'foo_s3cret',
    ],
];
```

Then, enable it :
```php
// config/modules.config.php
return [
    'ApigilityConsumer', <-- register here
    'Application'
],
```

Services
--------

**1. ApigilityConsumer\Service\ClientService**

For general Api Call, with usage:

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
    
    // token type and access token if required
    'token_type' =>  'token type if required, for example: "Bearer"',
    'access_token' => 'access token if required',
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

**2. ApigilityConsumer\Service\ClientAuthService**

It used for `oauth`, with usage:

```php
use ApigilityConsumer\Service\ClientAuthService;

$client = $serviceManager->get(ClientAuthService::class);

$data = [
    'api-route-segment' => '/oauth',
    'form-request-method' => 'POST',

    'form-data' => [
        'username' => 'foo', // not required if grant_type config = 'client_credentials' 
        'password' => '123', // not required if grant_type config = 'client_credentials' 
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

Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/ApigilityConsumer/blob/master/CONTRIBUTING.md)

