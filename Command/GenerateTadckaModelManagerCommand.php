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
use Tadcka\Bundle\GeneratorBundle\Generator\TadckaModelManagerGenerator;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 4:56 PM
 */
class GenerateTadckaModelManagerCommand extends GenerateTadckaCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tadcka:generate:model:manager')
            ->setDescription('Generates a new model manager inside a bundle')
            ->addOption('model', null, InputOption::VALUE_REQUIRED, 'The model class name to initialize (shortcut notation)');
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

        $dialog->writeSection($output, 'Model manager generation');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator();
        $generator->generate($bundle, $model);

        $output->writeln('Generating the model manager code: <info>OK</info>');

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
                'This command helps you generate Tadcka model managers.',
                '',
                'First, you need to give the model name you want to generate.',
                'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
                ''
            )
        );

        $modelInfo = $this->addModel($input, $output, $dialog);

        $input->setOption('model', $modelInfo['bundle_name'] . ':' . $modelInfo['model']);

        $summary = array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s:Manager:%s</info>\" Tadcka model manager", $modelInfo['bundle_name'], $modelInfo['model'] . 'Manager'),
            '',
        );

        // summary
        $output->writeln($summary);
    }

    private function addModel(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
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
        return new TadckaModelManagerGenerator($this->getContainer()->get('filesystem'));
    }
}
