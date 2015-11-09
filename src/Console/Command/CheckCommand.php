<?php

namespace SLLH\StyleCIFixers\Console\Command;

use Composer\Semver\Semver;
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

        $upToDate = true;
        foreach (Semver::sort($generator->getVersions()) as $version) {
            if ('dev-master' === $version) {
                continue;
            }

            $checkoutRet = $this->checkout($version);

            $output->write(sprintf('<info>%s</info>: ', $version));

            if (0 !== $checkoutRet) {
                $output->writeln('<error>KO</error>');
                $upToDate = false;
                if (true === $shouldBeUpdated) {
                    $generator->generate($version);
                    $this->commit($version);
                }
                continue;
            }

            $output->writeln('OK');
        }

        $this->checkout('master');

        return true === $upToDate ? 0 : 1;
    }

    private function commit($version)
    {
        $cwd = getcwd();
        chdir(self::REPOSITORY_PATH);

        exec('git add src/Fixers.php ');
        exec(sprintf('git commit -m "Fixers %s" --allow-empty 2>&1', $version));
        exec(sprintf('git tag -a %s -m %s 2>&1', $version, $version));
        chdir($cwd);
    }

    private function checkout($revision)
    {
        $cwd = getcwd();
        chdir(self::REPOSITORY_PATH);
        exec(sprintf('git checkout %s --force --quiet 2>&1', $revision), $output, $returnValue);
        chdir($cwd);

        return $returnValue;
    }
}
