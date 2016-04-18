<?php

$config = Symfony\CS\Config\Config::create()
    ->fixers(array(
        'short_array_syntax',
        'newline_after_open_tag',
        'ordered_use',
        'php_unit_construct',
        'php_unit_strict',
        'strict',
        'strict_param',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude('tests')
            ->notPath('src/Fixers.php')
            ->notName('*.php.twig')
            ->in(__DIR__)
    )
;

$config->setUsingCache(true);

if (method_exists($config, 'setRiskyAllowed')) {
    $config->setRiskyAllowed(true);
}

return $config;
