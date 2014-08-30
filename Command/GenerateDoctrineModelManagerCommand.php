<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tadcka\Bundle\GeneratorBundle\Generator\DoctrineModelManagerGenerator;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 4:58 PM
 */
class GenerateDoctrineModelManagerCommand extends GenerateTadckaCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tadcka:generate:model:doctrine_manager')
            ->setDescription('Generates a new doctrine model manager inside a bundle')
            ->addOption('model', null, InputOption::VALUE_REQUIRED, 'The model class name to initialize (shortcut notation)')
            ->addOption('db_driver', null, InputOption::VALUE_REQUIRED, 'Doctrine db driver (orm, mongodb)', 'orm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null === $input->getOption('model')) {
            throw new \RuntimeException('The model option must be provided.');
        }

        if (null === $input->getOption('db_driver')) {
            throw new \RuntimeException('The db driver option must be provided.');
        }

        list($bundle, $model) = $this->parseShortcutNotation($input->getOption('model'));
        if (is_string($bundle)) {
            $bundle = Validators::validateBundleName($bundle);

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundle));
            }
        }

        $generator = $this->getGenerator($bundle);
        $generator->generate($bundle, $model, $input->getOption('db_driver'));
    }

    /**
     * {@inheritdoc}
     */
    protected function createGenerator()
    {
        return new DoctrineModelManagerGenerator($this->getContainer()->get('filesystem'));
    }
}
