<?php

namespace SLLH\StyleCIFixers\StyleCI;

use StyleCI\SDK\Client;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class FixersGenerator
{
    /**
     * @var Client
     */
    private $styleCIClient;

    /**
     * @var array
     */
    private $fixersTab = [];

    /**
     * @var array[]
     */
    public static $aliasOptions = array(
        'align_double_arrow' => array(
            'binary_operator_spaces' => array(
                'align_double_arrow' => 'true'
            )
        ),
        'align_equals' => array(
            'binary_operator_spaces' => array(
                'align_equals' => 'true'
            )
        ),
        'unalign_double_arrow' => array(
            'binary_operator_spaces' => array(
                'align_double_arrow' => false,
            )
        ),
        'unalign_equals' => array(
            'binary_operator_spaces' => array(
                'align_equals' => false,
            )
        ),
        'concat_with_spaces' => array(
            'concat_spaces' => array(
                'spacing' => 'one'
            )
        ),
        'concat_without_spaces' => array(
            'concat_spaces' => array(
                'spacing' => 'none'
            )
        ),
        'echo_to_print' => array(
            'no_mixed_echo_print' => array(
                'use' => 'print'
            )
        ),
        'print_to_echo' => array(
            'no_mixed_echo_print' => array(
                'use' => 'echo'
            )
        ),
        'long_array_syntax' => array(
            'array_syntax' => array(
                'syntax' => 'long'
            )
        ),
        'short_array_syntax' => array(
            'array_syntax' => array(
                'syntax' => 'short'
            )
        ),
        'general_phpdoc_annotation_rename' => array(
            'phpdoc_no_alias_tag' => array(
                'property-read' => 'property',
                'property-write' => 'property'
            )
        ),
        'phpdoc_property' => array(
            'phpdoc_no_alias_tag' => array(
                'type' => 'var',
            )
        ),
        'phpdoc_type_to_var' => array(
            'phpdoc_no_alias_tag' => array(
                'type' => 'var',
            )
        ),
        'phpdoc_var_to_type' => array(
            'phpdoc_no_alias_tag' => array(
                'var' => 'type',
            )
        ),
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->styleCIClient = new Client();
    }

    /**
     * Generate Fixers.php file.
     *
     * @param bool $dryRun
     *
     * @return bool
     */
    public function generate($dryRun = true)
    {
        $classPath = __DIR__.'/../Fixers.php';
        $generatedClass = $this->getFixersClass();

        if (true === $dryRun) {
            $actualClass = file_get_contents($classPath);

            return 0 === strcmp($actualClass, $generatedClass);
        }

        return false !== file_put_contents($classPath, $generatedClass);
    }

    /**
     * Generate Fixers.php content.
     *
     * @return string
     */
    public function getFixersClass()
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/..'));

        $fixersTab = $this->getFixersTab();
        $presets = [];
        foreach ($fixersTab as $group => $fixers) {
            if (strstr($group, '_fixers')) {
                array_push($presets, str_replace('_fixers', '', $group));
            }
        }

        return $twig->render('StyleCI/Fixers.php.twig', ['fixersTab' => $fixersTab, 'presets' => $presets]);
    }

    /**
     * Returns fixers tab from StyleCI Config class.
     *
     * @return array
     */
    public function getFixersTab()
    {
        $this->makeFixersTab();

        return $this->fixersTab;
    }

    private function makeFixersTab()
    {
        $fixers = $this->styleCIClient->fixers();

        $this->fixersTab['valid'] = [];
        $this->fixersTab['risky'] = [];
        $this->fixersTab['aliases'] = [];
        $this->fixersTab['aliasOptions'] = static::$aliasOptions;
        $this->fixersTab['conflicts'] = [];

        foreach ($fixers as $fixer) {
            array_push($this->fixersTab['valid'], ['name' => $fixer['name']]);
            if (true === $fixer['risky']) {
                array_push($this->fixersTab['risky'], ['name' => $fixer['name']]);
            }
            foreach ($fixer['aliases'] as $alias) {
                array_push($this->fixersTab['aliases'], [
                    'key' => $alias,
                    'name' => $fixer['name'],
                ]);
            }
            if (null !== $fixer['conflict']) {
                array_push($this->fixersTab['conflicts'], [
                    'key' => $fixer['conflict'],
                    'name' => $fixer['name'],
                ]);
            }
        }

        $presets = $this->styleCIClient->presets();

        foreach ($presets as $preset) {
            $fixers = [];
            foreach ($preset['fixers'] as $fixerName) {
                array_push($fixers, ['name' => $fixerName]);
            }
            $this->fixersTab[$preset['name'].'_fixers'] = $fixers;
        }
    }
}
