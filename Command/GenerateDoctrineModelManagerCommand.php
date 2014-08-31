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

use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tadcka\Bundle\GeneratorBundle\Generator\DoctrineModelManagerGenerator;
use Tadcka\Bundle\GeneratorBundle\ModelManagerInfo;

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
            ->addOption('db-driver', null, InputOption::VALUE_REQUIRED, 'Set model manager db driver (orm, mongodb)', 'orm')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, or yml)', 'xml');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $model = Validators::validateModelName($input->getOption('model'));
        list($bundle, $model) = $this->parseShortcutNotation($model);
        $dbDriver = Validators::validateDbDriver($input->getOption('db-driver'));
        $format = Validators::validateFormat($input->getOption('format'));

        $dialog->writeSection($output, 'Doctrine model manager generation');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator();
        $generator->generate($bundle, $model, $dbDriver, $format);

        $output->writeln('Generating the doctrine model manager code: <info>OK</info>');

        $dialog->writeGeneratorSummary($output, array());
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Tadcka model manager generator');

        // namespace
        $output->writeln(
            array(
                '',
                'This command helps you generate Doctrine model managers.',
                '',
                'First, you need to give the model name you want to generate.',
                'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
                ''
            )
        );

        $modelInfo = $this->getModel($input, $output, $dialog);

        $input->setOption('model', $modelInfo['bundle_name'] . ':' . $modelInfo['model']);

        // db driver
        $output->writeln(
            array(
                '',
                'Determine the db driver to use for configuration model manager.',
                '',
            )
        );
        $dbDriver = $this->addDbDriver($input, $output, $dialog);
        $input->setOption('db-driver', $dbDriver);

        // format
        $output->writeln(
            array(
                '',
                'Determine the format to use for configuration files.',
                '',
            )
        );
        $format = $this->addFormat($input, $output, $dialog);
        $input->setOption('format', $format);

        $dbDrivers = ModelManagerInfo::getDoctrineManagerDrivers();
        $summary = array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s:Doctrine:" . $dbDrivers[$dbDriver] . ":%s</info>\" Doctrine model manager", $modelInfo['bundle_name'], $modelInfo['model'] . 'Manager'),
            '',
        );

        // summary
        $output->writeln($summary);
    }

    private function getModel(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());
        $modelValidator = array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateModelName');

        while (true) {
            $modelOption = $input->getOption('model');
            $modelQuestion = $dialog->getQuestion('The Model shortcut name', $modelOption);
            $model = $dialog->askAndValidate($output, $modelQuestion, $modelValidator, false, $modelOption, $bundleNames);

            list($bundleName, $model) = $this->parseShortcutNotation($model);

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);

                if (file_exists($bundle->getPath() . '/Model/' . str_replace('\\', '/', $model) . '.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Model "%s:%s" does not exist</>.', $bundleName, $model));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundleName));
            }
        }

        return array('bundle_name' => $bundleName, 'model' => $model);
    }

    /**
     * {@inheritdoc}
     */
    protected function createGenerator()
    {
        return new DoctrineModelManagerGenerator($this->getContainer()->get('filesystem'));
    }
}
