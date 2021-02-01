<?php // kahlan-config.php

use Kahlan\Filter\Filters;
use Laminas\Stdlib\ErrorHandler;

use const E_DEPRECATED;

// autoload hack
file_put_contents('vendor/laminas/laminas-zendframework-bridge/src/autoload.php', '');

Filters::apply($this, 'bootstrap', function($next) {

    $root = $this->suite()->root();
    $root->beforeAll(function () {
        ErrorHandler::start(E_DEPRECATED);
    });

    return $next();

});