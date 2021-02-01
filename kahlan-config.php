<?php // kahlan-config.php

use Kahlan\Filter\Filters;
use Laminas\Stdlib\ErrorHandler;

// autoload hack
file_put_contents('vendor/laminas/laminas-zendframework-bridge/src/autoload.php', '');

Filters::apply($this, 'bootstrap', function($next) {

    $root = $this->suite()->root();
    $root->beforeAll(function () {
        ErrorHandler::start(E_DEPRECATED);
        allow('stream_socket_client')->toBeCalled()->andRun(function() { });
        allow('Laminas\Http\Client\Adapter\Socket')->toReceive('write')->andRun(function() { return ''; });
    });

    return $next();

});