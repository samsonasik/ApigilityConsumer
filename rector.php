<?php

use Rector\Config\RectorConfig;
use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $containerConfigurator): void {
    $containerConfigurator->import(SetList::CODING_STYLE);
    $containerConfigurator->import(SetList::CODE_QUALITY);
    $containerConfigurator->import(SetList::EARLY_RETURN);
    $containerConfigurator->import(SetList::NAMING);
    $containerConfigurator->import(LevelSetList::UP_TO_PHP_80);
    $containerConfigurator->import(SetList::TYPE_DECLARATION);
    $containerConfigurator->import(SetList::TYPE_DECLARATION_STRICT);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::PATHS, [
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/spec',
        __DIR__ . '/rector.php'
    ]);

    $parameters->set(Option::AUTO_IMPORT_NAMES, true);
};
