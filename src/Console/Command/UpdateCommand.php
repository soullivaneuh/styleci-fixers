<?php

namespace SLLH\StyleCIFixers\Console\Command;

use SLLH\StyleCIFixers\StyleCI\FixersGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class UpdateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Update StyleCI fixers config from official repository.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new FixersGenerator();
        $generator->generate();
    }
}
