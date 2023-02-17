<?php

declare (strict_types=1);
namespace RectorPrefix202302;

use Rector\Config\RectorConfig;
use Rector\PHPUnit\Rector\StaticCall\GetMockRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(GetMockRector::class);
};
