<?php

namespace ApigilityConsumer\Spec;

use ApigilityConsumer\ConfigProvider;

describe('ConfigProvider', function (): void {
    beforeAll(function (): void {
        $this->configProvider = new ConfigProvider();
    });

    describe('->__invoke', function (): void {

        it('return "config" array with "dependencies" key', function (): void {

            $moduleConfig = include __DIR__ . '/../config/module.config.php';
            $expected = [
                'dependencies' => $moduleConfig['service_manager'],
            ];

            $actual = $this->configProvider->__invoke();
            expect($actual)->toBe($expected);

        });

    });

});
