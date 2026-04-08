<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'yoda_style' => [
            'equal' => true,
            'identical' => true,
            'less_and_greater' => true,
        ],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'yield'],
        ],
        'final_class' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__.'/src')
            ->exclude(['var', 'vendor', 'tests'])
    )
    ->setCacheFile(__DIR__.'/var/.php-cs-fixer.cache')
    ->setRiskyAllowed(true);
