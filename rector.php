<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\PHPOffice\Set\PHPOfficeSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/ajax',
        __DIR__ . '/api',
        __DIR__ . '/bling',
        __DIR__ . '/helpintegracao',
        __DIR__ . '/helpintegracaobraip',
        __DIR__ . '/includes',
        __DIR__ . '/mercado-pago',
        __DIR__ . '/pagseguro',
        __DIR__ . '/seguro.dropexpress',
        __DIR__ . '/views',
    ]);
    
    $rectorConfig->sets([
        PHPOfficeSetList::PHPEXCEL_TO_PHPSPREADSHEET
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_80
    //    ]);
};
