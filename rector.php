<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\String_\SimplifyQuoteEscapeRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/bootstrap',
        __DIR__ . '/config',
        __DIR__ . '/public',
        __DIR__ . '/resources',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(php83: true)
    ->withComposerBased(laravel: true)
    ->withPreparedSets(codeQuality: true)
    ->withSets([
        LaravelSetList::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
    ])
    ->withRules([
        ClassPropertyAssignToConstructorPromotionRector::class,
        SeparateMultiUseImportsRector::class,
        SimplifyQuoteEscapeRector::class,
    ]);
