<?php

namespace SLLH\StyleCIFixers\Console\Command;

use SLLH\StyleCIFixers\StyleCI\FixersGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
class CheckCommand extends Command
{
    const REPOSITORY_PATH = __DIR__.'/../../..';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('check')
            ->setDescription('Check if StyleCI fixers config is up to date.')
            ->addOption('update', 'u', InputOption::VALUE_NONE)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shouldBeUpdated = $input->getOption('update');
        $generator = new FixersGenerator();
        $upToDate = $generator->generate(!$shouldBeUpdated);

        if (true === $upToDate) {
            $output->writeln('<info>[OK]</info>');
        } else {
            $output->writeln('<error>[KO]</error>');
        }

        return true === $upToDate ? 0 : 1;
    }
}
