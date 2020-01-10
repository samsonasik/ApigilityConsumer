ApigilityConsumer
=================

[![Latest Version](https://img.shields.io/github/release/samsonasik/ApigilityConsumer.svg?style=flat-square)](https://github.com/samsonasik/ApigilityConsumer/releases)
[![Build Status](https://travis-ci.org/samsonasik/ApigilityConsumer.svg?branch=master)](https://travis-ci.org/samsonasik/ApigilityConsumer)
[![Coverage Status](https://coveralls.io/repos/github/samsonasik/ApigilityConsumer/badge.svg?branch=master)](https://coveralls.io/github/samsonasik/ApigilityConsumer?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)
[![Downloads](https://poser.pugx.org/samsonasik/apigility-consumer/downloads)](https://packagist.org/packages/samsonasik/apigility-consumer)

Laminas API Tools Client module to consume API Services.

> This is README for version ^3.0 which only support php ^7.1 with laminas-servicemanager v3 and laminas-json v3

> For version ^2.0, you can read at [version 2 readme](https://github.com/samsonasik/ApigilityConsumer/tree/2.x.x) which only support php ^7.1 with zend-servicemanager v3 and zend-json v3

> For version 1, you can read at [version 1 readme](https://github.com/samsonasik/ApigilityConsumer/tree/1.x.x) which still support php ^5.6|^7.0 with zend-servicemanager v2.

> Consider upgrading :)

Installation
------------

Installation of this module uses [composer](https://getcomposer.org/).

```sh
composer require samsonasik/apigility-consumer
```

For its configuration, copy `vendor/samsonasik/apigility-consumer/config/apigility-consumer.local.php.dist` to `config/autoload/apigility-consumer.local.php` and configure with your api host url (required), oauth, and/or http auth settings:

```php
use Laminas\Http\Client as HttpClient;

return [
    'apigility-consumer' => [
        'api-host-url' => 'http://api.host.com',

        // null for default or array of configuration listed at https://docs.zendframework.com/zend-http/client/intro/#configuration
        'http_client_options' => null,

        // for oauth
        'oauth' => [

            //default selected client
            'grant_type'    => 'password', // or client_credentials
            'client_id'     => 'foo',
            'client_secret' => 'foo_s3cret',

            // multiple clients to be selected
            'clients' => [
                'foo' => [ // foo is client_id
                    'grant_type'    => 'password', // or client_credentials
                    'client_secret' => 'foo_s3cret',
                ],
                'bar' => [ // bar is client_id
                    'grant_type'    => 'password', // or client_credentials
                    'client_secret' => 'bar_s3cret',
                ],
            ],

        ],

        // for basic and or digest
        'auth' => [

            // default client
            HttpClient::AUTH_BASIC => [
                'username' => 'foo',
                'password' => 'foo_s3cret'
            ],

            HttpClient::AUTH_DIGEST => [
                'username' => 'foo',
                'password' => 'foo_s3cret'
            ],

            // multiple clients to be selected
            'clients' => [
                'foo' => [ // foo is key represent just like "client_id" to ease switch per-client config
                    HttpClient::AUTH_BASIC => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],

                    HttpClient::AUTH_DIGEST => [
                        'username' => 'foo',
                        'password' => 'foo_s3cret'
                    ],
                ],
                'bar' => [ // bar is key represent just like "client_id" to ease switch per-client config
                    HttpClient::AUTH_BASIC => [
                        'username' => 'bar',
                        'password' => 'bar_s3cret'
                    ],

                    HttpClient::AUTH_DIGEST => [
                        'username' => 'bar',
                        'password' => 'bar_s3cret'
                    ],
                ],
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
    'Application',
],
```

Using at Mezzio
------------------------
You can use at Mezzio, after set up local `config/autoload/apigility-consumer.local.php` like above, you can copy `config/mezzio.local.php.dist` to `config/autoload/mezzio.local.php`, and you can use it.


Services
--------

**1. ApigilityConsumer\Service\ClientAuthService**

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

**Specify Oauth "client_id"**

You can specify what client_id to be used on Http Auth with provide `withClient()`:

```php
$clientResult = $client->withClient('bar') // bar is "client_id" defined in clients in oauth config
                       ->callAPI($data, $timeout);
```

**Reset  Oauth "client_id"**

We can re-use the client service and use back default "client_id" with `resetClient()`:

```php
$clientResult = $client->withClient('bar') // bar is "client_id" defined in clients in auth config
                       ->callAPI($data, $timeout);

$clientResultDefault = $client->resetClient()
                              ->callAPI($data, $timeout);
```

**2. ApigilityConsumer\Service\ClientService**

For general Api Call, with usage:

**a. General RAW Json data**

```php
use ApigilityConsumer\Service\ClientService;

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

$client = $serviceManager->get(ClientService::class);

$timeout  = 100;
$clientResult = $client->callAPI($data, $timeout);
```

**b. With Upload file**

You can also do upload with it to upload file to API Service. For example:

```php
use ApigilityConsumer\Service\ClientService;

$data['api-route-segment']   = '/api';
$data['form-request-method'] = 'POST';

$data['form-data']           = $request->getPost()->toArray();
$data['form-data']['files']  = $request->getFiles()->toArray();

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

$client = $serviceManager->get(ClientService::class);

$timeout  = 100;
$clientResult = $client->callAPI($data, $timeout);
```

**With include Http (basic or digest) Authentication**

if api call require authentication for basic or digest, you can apply `->withHttpAuthType()`:

```php
use Laminas\Http\Client as HttpClient;

$clientResult = $client->withHttpAuthType(HttpClient::AUTH_BASIC)
                       ->callAPI($data, $timeout);
// OR
$clientResult = $client->withHttpAuthType(HttpClient::AUTH_DIGEST)
                       ->callAPI($data, $timeout);
```

that will read of specified basic or digest auth config we defined at `config/autoload/apigility-consumer.local.php`.

If you want to specify custom username and password for the Http Auth on `callAPI()` call, you can specify via `$data`:

```php
use Laminas\Http\Client as HttpClient;

$data = [
    'api-route-segment' => '/api',
    'form-request-method' => 'POST',

    'form-data' => [
        // fields that will be used as raw json to be sent
        'foo' => 'fooValue',
    ],

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
];

$clientResult = $client->withHttpAuthType(HttpClient::AUTH_BASIC)
                       ->callAPI($data, $timeout);
// OR
$clientResult = $client->withHttpAuthType(HttpClient::AUTH_DIGEST)
                       ->callAPI($data, $timeout);
```

**Specify "client_id" on Http Auth**

On Http Auth, there is no "client_id" definition concept. On `ClientService`, they are keys that represent just like "client_id" to ease switch client specific http auth.

To allow You can specify what "client_id" to be used on Http Auth with provide `withClient()`:

```php
$clientResult = $client->withClient('bar') // bar is "client_id" defined in clients in auth config
                       ->withHttpAuthType(HttpClient::AUTH_BASIC)
                       ->callAPI($data, $timeout);
```

**Reset "client_id" Http Auth**

We can re-use the client service and use back default "client_id" with `resetClient()`:

```php
$clientResult = $client->withClient('bar') // bar is "client_id" defined in clients in auth config
                       ->withHttpAuthType(HttpClient::AUTH_BASIC)
                       ->callAPI($data, $timeout);

$clientResultDefault = $client->resetClient()
                              ->callAPI($data, $timeout);
```

**Reset Http Auth Type**

After one or both `HttpClient::AUTH_BASIC` or `HttpClient::AUTH_DIGEST` used, we can re-use the client service and use back normal API Call without Http Authentication with apply `->resetHttpAuthType()`:

```php
$clientResultWithBasicAuth   = $client->withHttpAuthType(HttpClient::AUTH_BASIC)
                                      ->callAPI($data1, $timeout);
$clientResultWithDigestAuth  = $client->withHttpAuthType(HttpClient::AUTH_DIGEST)
                                      ->callAPI($data2, $timeout);

// RESET IT TO NORMAL WITHOUT HTTP AUTHENTICATION
$clientResultWithoutHttpAuth = $client->resetHttpAuthType()
                                      ->callAPI($data3, $timeout);
```

Client Result of callAPI() returned usage
-----------------------------------------

The `$clientResult` will be a `ApigilityConsumer\Result\ClientResult` or `ApigilityConsumer\Result\ClientAuthResult` instance, with this instance, you can do:

```php
//...
$clientResult = $client->callAPI($data, $timeout);

if (! $clientResult->success) {
    var_dump($clientResult::$messages);
} else {
    var_dump($clientResult->data);
}
```

Contributing
------------
Contributions are very welcome. Please read [CONTRIBUTING.md](https://github.com/samsonasik/ApigilityConsumer/blob/master/CONTRIBUTING.md)
