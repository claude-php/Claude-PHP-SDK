<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('vendor')
    ->name('*.php')
    ->notName('*.blade.php');

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PSR12' => true,
        '@PhpCsFixer' => true,
        '@PHP81Migration' => true,
        
        // Code organization
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'global_namespace_import' => false,
        
        // Code style preferences
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters']
        ],
        
        // Documentation
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_summary' => false,
        'phpdoc_separation' => true,
        'phpdoc_order' => true,
        
        // Strict types
        'declare_strict_types' => true,
        'strict_param' => true,
        'strict_comparison' => true,
        
        // Modern PHP features
        'modernize_types_casting' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        
        // Security and best practices
        'no_php4_constructor' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => false
        ],
        
        // Exception to allow longer lines for type annotations
        'line_ending' => true,
        
        // Disable some overly strict rules
        'php_unit_test_class_requires_covers' => false,
        'php_unit_internal_class' => false,
        'final_internal_class' => false,
        'comment_to_phpdoc' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);