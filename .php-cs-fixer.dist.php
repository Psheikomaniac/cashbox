<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->exclude(['var', 'vendor'])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        '@PHP84Migration' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => true,
        'no_unused_imports' => true,
        'declare_strict_types' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_separation' => true,
        'new_with_braces' => false,
        'new_with_parentheses' => true,
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'visibility_required' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'phpdoc_to_comment' => false,
        'phpdoc_add_missing_param_annotation' => true,
        'modernize_types_casting' => true,
        'modernize_strpos' => true,
        'use_arrow_functions' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
;