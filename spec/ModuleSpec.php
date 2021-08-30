<?php

namespace ApigilityConsumer\Spec;

use ApigilityConsumer\Module;

describe('Module', function (): void {
    beforeAll(function (): void {
        $this->module = new Module();
    });

    describe('->getConfig', function (): void {

        it('return "config" array', function (): void {

            $moduleConfig = include __DIR__ . '/../config/module.config.php';

            $actual = $this->module->getConfig();
            expect($actual)->toBe($moduleConfig);

        });

    });

});
