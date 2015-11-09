<?php

namespace SLLH\StyleCIFixers\StyleCI;

use Packagist\Api\Client;
use Symfony\CS\Tokenizer\Token;
use Symfony\CS\Tokenizer\Tokens;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class FixersGenerator
{
    const STYLE_CI_CLASS_FILE = 'https://github.com/StyleCI/Config/raw/master/src/Config.php';

    /**
     * @var Client
     */
    private $packagistClient;

    /**
     * @var array
     */
    private $fixersTab = array();

    public function __construct()
    {
        $this->packagistClient = new Client();
    }

    /**
     * @return string[]
     */
    public function getVersions()
    {
        $configPackage = $this->packagistClient->get('styleci/config');

        return array_map(function ($version) {
            return $version->getVersion();
        }, $configPackage->getVersions());
    }

    /**
     * Generate Fixers.php file.
     *
     * @param string $version
     */
    public function generate($version)
    {
        file_put_contents(__DIR__.'/../Fixers.php', $this->getFixersClass($version));
    }

    /**
     * Generate Fixers.php content.
     *
     * @param string $version
     *
     * @return string
     */
    public function getFixersClass($version)
    {
        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/..'));

        $fixersTab = $this->getFixersTab($version);
        $presets = array();
        foreach ($fixersTab as $group => $fixers) {
            if (strstr($group, '_fixers')) {
                array_push($presets, str_replace('_fixers', '', $group));
            }
        }

        return $twig->render('StyleCI/Fixers.php.twig', array('fixersTab' => $fixersTab, 'presets' => $presets));
    }

    /**
     * Returns fixers tab from StyleCI Config ckass.
     *
     * @param string $version
     *
     * @return array
     */
    public function getFixersTab($version)
    {
        $this->makeFixersTab($version);

        return $this->fixersTab;
    }

    private function makeFixersTab($version)
    {
        $configClass = file_get_contents('https://raw.githubusercontent.com/StyleCI/Config/'.$version.'/src/Config.php');

        /** @var Tokens|Token[] $tokens */
        $tokens = Tokens::fromCode($configClass);
        /*
         * @var int
         * @var Token
         */
        foreach ($tokens->findGivenKind(T_CONST) as $index => $token) {
            if ('[' === $tokens[$index + 6]->getContent()) {
                $name = strtolower($tokens[$index + 2]->getContent());
                $fixers = array();
                for ($i = $index + 7; ']' !== $tokens[$i]->getContent(); ++$i) {
                    if ($tokens[$i]->isGivenKind(T_CONSTANT_ENCAPSED_STRING) && ',' === $tokens[$i + 1]->getContent()) {
                        // Simple array management
                        array_push($fixers, array('name' => $this->getString($tokens[$i]->getContent())));
                    } elseif ($tokens[$i]->isGivenKind(T_CONSTANT_ENCAPSED_STRING)) {
                        // Double arrow management
                        $key = $this->getString($tokens[$i]->getContent());
                        for (++$i; $tokens[$i]->isGivenKind(T_DOUBLE_ARROW); ++$i) {
                        }
                        $i += 3;
                        array_push($fixers, array(
                            'key'  => $key,
                            'name' => $this->getString($tokens[$i]->getContent()),
                        ));
                    }
                }
                $this->fixersTab[$name] = $fixers;
            }
        }
    }

    /**
     * @param string $tokenContent
     *
     * @return string
     */
    private function getString($tokenContent)
    {
        return str_replace(array('"', "'"), '', $tokenContent);
    }
}
