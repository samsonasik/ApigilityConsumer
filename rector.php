<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Closure\StaticClosureRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::DEAD_CODE,
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        LevelSetList::UP_TO_PHP_80,
        SetList::TYPE_DECLARATION,
    ]);

    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/spec',
        __DIR__ . '/rector.php',
    ]);
    $rectorConfig->importNames();

    $rectorConfig->skip([
        StaticArrowFunctionRector::class => [
            __DIR__ . '/spec',
        ],
        StaticClosureRector::class       => [
            __DIR__ . '/spec',
        ],
    ]);
};
