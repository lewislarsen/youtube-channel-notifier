<?php

declare(strict_types=1);

use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use RectorLaravel\Set\LaravelLevelSetList;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withSets([
        SetList::NAMING,
        SetList::CARBON,
        SetList::EARLY_RETURN,
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
        SetList::PHP_83,
        SetList::PHP_84,
        SetList::INSTANCEOF,
        LaravelSetList::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL,
        LaravelSetList::LARAVEL_CODE_QUALITY,
        LaravelLevelSetList::UP_TO_LARAVEL_110,
    ])->withRules([
            DeclareStrictTypesRector::class,
            ClassPropertyAssignToConstructorPromotionRector::class,
            SeparateMultiUseImportsRector::class,
            SymplifyQuoteEscapeRector::class,
            WrapEncapsedVariableInCurlyBracesRector::class,
        ])
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
