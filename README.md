ApigilityConsumer
=================

[![Latest Version](https://img.shields.io/github/release/samsonasik/ApigilityConsumer.svg?style=flat-square)](https://github.com/samsonasik/ApigilityConsumer/releases)
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
use Zend\Http\Client as HttpClient;

return [
    'apigility-consumer' => [
        'api-host-url' => 'http://api.host.com',
        
        // for oauth
        'oauth' => [
            'grant_type'    => 'password', // or client_credentials
            'client_id'     => 'foo',
            'client_secret' => 'foo_s3cret',
        ],
        
        // for basic and or digest
        'auth' => [
            
            HttpClient::AUTH_BASIC => [
                'username' => 'foo',
                'password' => 'foo_s3cret'
            ],
            
            HttpClient::AUTH_DIGEST => [
                'username' => 'foo',
                'password' => 'foo_s3cret'
            ],
            
        ],
    ],
];
```

Then, enable it :
```php
// config/modules.config.php
return [
    'ApigilityConsumer', // <-- register here
    'Application'
],
```

Using at Zend\Expressive
------------------------
You can use at Zend\Expressive, after set up local `config/autoload/apigility-consumer.local.php` like above, you can copy `config/expressive.local.php.dist` to `config/autoload/expressive.local.php`, and you can use it.


Services
--------

**1. ApigilityConsumer\Service\ClientService**

For general Api Call, with usage:

**a. General RAW Json data**

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

**b. With Upload file**

You can also do upload with it to upload file to API Service. For example:

```php
$data['form-data']          = $request->getPost()->toArray();
$data['form-data']['files'] = $request->getFiles()->toArray();

/** data['form-data'] should be containst like the following
[
    'regular_key1' => 'regular_keyValue1',
    'regular_key2' => 'regular_keyValue2',
    
    'files' => [
        'file1' => [
            'type' => 'text/csv',
            'name' => 'file.csv',
            'tmp_name' => '/path/to/tmp/file',
            'error' => 'UPLOAD_ERR_OK',
            'size' => 123,
        ],
        'file2' => [
            'type' => 'text/csv',
            'name' => 'file2.csv',
            'tmp_name' => '/path/to/tmp/file2',
            'error' => 'UPLOAD_ERR_OK',
            'size' => 123,
        ],
    ],
]
*/

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

**With include Http (basic or digest) Authentication**

if api call require authentication for basic or digest, you can apply `->withHttpAuthType()`:

```php
use Zend\Http\Client as HttpClient;

$clientResult = $client->withHttpAuthType(HttpClient::AUTH_BASIC)
                       ->callAPI($data, $timeout);
// OR
$clientResult = $client->withHttpAuthType(HttpClient::AUTH_DIGEST)
                       ->callAPI($data, $timeout);
```

that will read of specified basic or digest auth config we defined at  config/autoload/apigility-consumer.local.php.

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

