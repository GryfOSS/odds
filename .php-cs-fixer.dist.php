<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('node_modules')
    ->exclude('vendor')
    ->exclude('var')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
    $config->setRules([
        '@PSR12' => true,
        'strict_param' => true,
        'array_syntax' => ['syntax' => 'short'],
        '@Symfony' => true,
        'full_opening_tag' => true,
        'declare_strict_types' => true,
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'array_push' => true,
        'backtick_to_shell_exec' => true,
        'binary_operator_spaces' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'braces' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'compact_nullable_typehint' => true,
        'concat_space' => true,
        'constant_case' => true,
        'date_time_immutable' => true,
        'dir_constant' => true,
        '@DoctrineAnnotation' => true,
        'ereg_to_preg' => true,
        'encoding' => true,
        'heredoc_indentation' => true,
        'heredoc_to_nowdoc' => true,
        'is_null' => true,
        'mb_str_functions' => true,
        'method_chaining_indentation' => true,
        'multiline_comment_opening_closing' => true,
        'native_function_invocation' => false,
        'native_function_type_declaration_casing' => true,
        'native_function_casing' => true,
        'native_constant_invocation' => false,
        'strict_comparison' => true,
        'ternary_to_null_coalescing' => true,
        'use_arrow_functions' => true
    ])
    ->setIndent("    ")
    ->setLineEnding("\n")
    ->setFinder($finder);

return $config;