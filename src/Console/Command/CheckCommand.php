<?php

namespace SLLH\StyleCIFixers\Console\Command;

use SLLH\StyleCIFixers\StyleCI\FixersGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class CheckCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Check if StyleCI fixers config is up to date.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fixersClass = file_get_contents(__DIR__.'/../../StyleCI/Fixers.php');

        $generator = new FixersGenerator();

        if ($fixersClass === $generator->getFixersClass()) {
            $output->writeln('StyleCI fixers are up to date.');
        } else {
            $output->writeln('<error>StyleCI fixers are out of date. Run update command to fix it.</error>');

            return 1;
        }

        return 0;
    }
}
