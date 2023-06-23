<?php

declare(strict_types=1);

$finder = Symfony\Component\Finder\Finder::create()
    ->notPath('vendor')
    ->in(__DIR__)
    ->name('*.php');

$config = new PhpCsFixer\Config();

$config
    ->setUsingCache(false)
    ->setFinder($finder)
    ->setRules(
        [
            '@PSR12' => true,
            'final_class' => true,
            'static_lambda' => false,
            'linebreak_after_opening_tag' => true,
            'blank_line_after_opening_tag' => true,
            'declare_strict_types' => true,
            'array_syntax' => [
                'syntax' => 'short'
            ],
            'ordered_imports' => [
                'sort_algorithm' => 'length'
            ],
            'no_unused_imports' => true,
            'list_syntax' => [
                'syntax' => 'short',
            ],
            'native_function_invocation' => [
                'include' => []
            ],
            'no_leading_import_slash' => false,
            'native_constant_invocation' => false,
            'native_function_casing' => true,
            'global_namespace_import' => [
                'import_classes' => true,
                'import_functions' => false,
                'import_constants' => false,
            ],
            'lowercase_cast' => true,
            'lowercase_static_reference' => true,
            'modernize_types_casting' => true,
            'new_with_braces' => true,
            'blank_line_before_statement' => [
                'statements' => ['declare'],
            ],
            'return_type_declaration' => [
                'space_before' => 'none',
            ],
        ]
    );

return $config;
