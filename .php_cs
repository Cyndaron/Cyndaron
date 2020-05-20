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

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_like'],
        'array_syntax' => ['syntax' => 'short'],
        'braces' => [
            'allow_single_line_closure' => true,
            'position_after_control_structures' => 'next',
            'position_after_functions_and_oop_constructs' => 'next',
            'position_after_anonymous_constructs' => 'next',
        ],
        'constant_case' => ['case' => 'lower'],
        'linebreak_after_opening_tag' => true,
        'phpdoc_order' => true,
        'strict_param' => true,
        'string_line_ending' => true,
    ])
    ->setFinder($finder);