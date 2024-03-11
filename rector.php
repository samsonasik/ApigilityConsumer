<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPreparedSets(
        deadCode: true,
        codingStyle: true,
        codeQuality: true,
        earlyReturn: true,
        naming: true,
        typeDeclarations: true
    )
    ->withPhpSets(php80: true)
    ->withPaths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/spec',
    ])
    ->withRootFiles()
    ->withImportNames()
    ->withSkip([
        StaticArrowFunctionRector::class => [
            __DIR__ . '/spec',
        ],
        StaticClosureRector::class       => [
            __DIR__ . '/spec',
        ],
    ]);
