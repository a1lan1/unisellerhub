<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\ValueObject\PhpVersion;
use RectorLaravel\Rector\ClassMethod\ScopeNamedClassMethodToScopeAttributedClassMethodRector;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelSetProvider;

return RectorConfig::configure()
    // Paths to analyze
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/resources',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])

    // Skip rules for specific paths to prevent breaking Laravel's magic
    ->withSkip([
        '*/vendor/*',
        '*/storage/*',
        '*/bootstrap/cache/*',
    ])

    // Enable caching for Rector
    ->withCache(
        cacheDirectory: __DIR__.'/storage/rector',
        cacheClass: FileCacheStorage::class
    )

    // Import names
    ->withImportNames(
        removeUnusedImports: true,
    )

    // Define the Rector rules to apply
    ->withRules([
        DeclareStrictTypesRector::class,
        ScopeNamedClassMethodToScopeAttributedClassMethodRector::class,
    ])

    // Set providers (required for Laravel sets)
    ->withSetProviders(LaravelSetProvider::class)

    // PHP version
    ->withPhpVersion(PhpVersion::PHP_84)

    // PHP sets
    ->withPhpSets(php84: true)

    // Attributes sets for modern PHP attributes
    ->withAttributesSets(
        symfony: true,
        doctrine: true,
        phpunit: true,
    )

    // Prepared sets for common code quality rules
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        phpunitCodeQuality: true
    )

    // Composer-based rules
    ->withComposerBased(
        phpunit: true,
        laravel: true
    )

    // Apply sets for Laravel
    ->withSets([
        LaravelSetList::LARAVEL_130,
        LaravelSetList::LARAVEL_COLLECTION,
        LaravelSetList::LARAVEL_CODE_QUALITY,
    ]);
