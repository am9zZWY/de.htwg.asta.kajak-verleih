<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\DowngradeSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/'
    ]);

    $rectorConfig->import(DowngradeSetList::PHP_81);
    $rectorConfig->import(DowngradeSetList::PHP_80);
    $rectorConfig->import(DowngradeSetList::PHP_74);
    $rectorConfig->import(DowngradeSetList::PHP_73);
    $rectorConfig->import(DowngradeSetList::PHP_72);
};
