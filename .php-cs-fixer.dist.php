<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
$config
    ->setRules([
        '@PHP80Migration:risky' => true,
        '@Symfony:risky' => true,
        '@Symfony' => true,
        'binary_operator_spaces' => ['operators' => ['|' => null]], // https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/5495
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align' => ['align' => 'left'],
        'use_arrow_functions' => false,
    ])
    ->setFinder($finder);

return $config;
