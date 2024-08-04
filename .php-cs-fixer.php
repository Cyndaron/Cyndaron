<?php
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */

$finder = PhpCsFixer\Finder::create()
    ->notPath('cache')
    ->notPath('vendor')
    ->in(__DIR__)
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR2' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_like'],
        'array_syntax' => ['syntax' => 'short'],
        'braces_position' => [
            'allow_single_line_anonymous_functions' => true,
            'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
            'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
        ],
        'constant_case' => ['case' => 'lower'],
        'control_structure_continuation_position' => [
            'position' => 'next_line',
        ],
        'function_declaration' => ['closure_function_spacing' => 'none'],
        'linebreak_after_opening_tag' => true,
        'phpdoc_order' => true,
        'strict_param' => true,
        'string_line_ending' => true,
        'native_function_invocation' => [
            'include' => ['@all'],
        ],
        'global_namespace_import' => [
            'import_classes' => null,
            'import_constants' => true,
            'import_functions' => true,
        ]
    ])
    ->setFinder($finder);
