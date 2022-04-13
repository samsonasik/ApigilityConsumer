<?php

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        SetList::CODING_STYLE,
        SetList::CODE_QUALITY,
        SetList::EARLY_RETURN,
        SetList::NAMING,
        LevelSetList::UP_TO_PHP_80,
        SetList::TYPE_DECLARATION,
        SetList::TYPE_DECLARATION_STRICT
    ]);

    $rectorConfig->paths([
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/spec',
        __DIR__ . '/rector.php'
    ]);
    $rectorConfig->importNames();
};
