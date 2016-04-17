<?php

namespace SLLH\StyleCIFixers\Console;

use SLLH\StyleCIFixers\Console\Command\CheckCommand;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->add(new CheckCommand());
    }
}
