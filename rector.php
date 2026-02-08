<?php

declare(strict_types=1);
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        naming: true,
        earlyReturn: true
    )
    ->withPhpSets(php80: true)
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/spec',
    ])
    ->withRootFiles()
    ->withImportNames(removeUnusedImports: true);
