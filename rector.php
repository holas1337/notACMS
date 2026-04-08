<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Rector\Doctrine\CodeQuality\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
use Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector;
use Rector\Symfony\Set\SymfonySetList;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\Symfony\CodeQuality\Rector\Class_\ControllerMethodInjectionToConstructorRector;

// See more here
// https://github.com/rectorphp/rector/blob/main/src/Configuration/RectorConfigBuilder.php
// https://getrector.com/documentation/set-lists
return RectorConfig::configure()
    // configure paths to check
    ->withPaths([__DIR__ . "/src"])
    // configure paths to skip
    ->withSkip([__DIR__ . "/src/Migrations", __DIR__ . "/src/Kernel.php"])
    // configure Set list for PHP versions
    ->withPhpSets(php85: true)
    // configure prepared sets from Rector
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        carbon: false,
        rectorPreset: true,
        phpunitCodeQuality: true,
        doctrineCodeQuality: true,
        symfonyCodeQuality: true,
        symfonyConfigs: true,
    )
    ->withComposerBased(symfony: true)
    // configure attributes sets
    ->withAttributesSets(symfony: true, doctrine: true)
    // configure custom set lists
    ->withSets([])
    // configure single rules - https://getrector.com/find-rule
    ->withRules([])
    ->withSkip([]);
