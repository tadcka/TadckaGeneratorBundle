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
use Tadcka\Bundle\GeneratorBundle\Generator\TadckaModelGenerator;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 12:22 PM
 */
class GenerateTadckaModelCommand extends GenerateTadckaCommand
{
    protected function configure()
    {
        $this
            ->setName('tadcka:generate:model')
            ->setDescription('Generates a new model inside a bundle')
            ->addOption('model', null, InputOption::VALUE_REQUIRED, 'The model class name to initialize (shortcut notation)')
            ->addOption('fields', null, InputOption::VALUE_REQUIRED, 'The fields to create with the new model')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, or yml)', 'xml')
            ->addOption('with-manager', null, InputOption::VALUE_NONE, 'Whether to generate the model manager or not')
            ->setHelp(<<<EOT
The <info>tadcka:generate:model</info> task Tadcka generates a new
model inside a bundle:

<info>php app/console tadcka:generate:model --model=AcmeBlogBundle:Blog/Post</info>

The above command would initialize a new model in the following model
namespace <info>Acme\BlogBundle\Model\Blog\Post</info>.

You can also optionally specify the fields you want to Tadcka generate in the new
model:

<info>php app/console tadcka:generate:model --model=AcmeBlogBundle:Blog/Post --fields="title:string(255) body:text"</info>

The command can also generate the corresponding model manager class with the
<comment>--with-manager</comment> option:

<info>php app/console tadcka:generate:model --model=AcmeBlogBundle:Blog/Post --with-manager</info>

By default, the command uses annotations for the mapping information; change it
with <comment>--format</comment>:

<info>php app/console tadcka:generate:model --model=AcmeBlogBundle:Blog/Post --format=yml</info>

To deactivate the interaction mode, simply use the `--no-interaction` option
without forgetting to pass all needed options:

<info>php app/console tadcka:generate:model --model=AcmeBlogBundle:Blog/Post --format=xml --fields="title:string(255) body:text" --with-manager --no-interaction</info>
EOT
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();

        if ($input->isInteractive()) {
            if (!$dialog->askConfirmation($output, $dialog->getQuestion('Do you confirm generation', 'yes', '?'), true)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'Welcome to the Tadcka model generator');

        // namespace
        $output->writeln(
            array(
                '',
                'This command helps you generate Tadcka models.',
                '',
                'First, you need to give the model name you want to generate.',
                'You must use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>.',
                ''
            )
        );

        $bundleNames = array_keys($this->getContainer()->get('kernel')->getBundles());

        while (true) {
            $model = $dialog->askAndValidate(
                $output,
                $dialog->getQuestion('The Model shortcut name', $input->getOption('model')),
                array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateModelName'),
                false,
                $input->getOption('model'),
                $bundleNames
            );

            list($bundleName, $model) = $this->parseShortcutNotation($model);

            try {
                $bundle = $this->getContainer()->get('kernel')->getBundle($bundleName);

                if (!file_exists($bundle->getPath().'/Model/'.str_replace('\\', '/', $model).'.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Entity "%s:%s" already exists</>.', $bundleName, $model));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundleName));
            }
        }
        $input->setOption('model', $bundleName.':'.$model);

        // format
        $output->writeln(
            array(
                '',
                'Determine the format to use for the mapping information.',
                '',
            )
        );

        $formats = array('yml', 'xml', 'php');

        $format = $dialog->askAndValidate(
            $output,
            $dialog->getQuestion(
                'Configuration format (yml, xml, or php)',
                $input->getOption('format')
            ),
            array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'),
            false,
            $input->getOption('format'),
            $formats
        );
        $input->setOption('format', $format);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $dialog));

        // manager?
        $output->writeln('');
        $withModel = $dialog->askConfirmation(
            $output,
            $dialog->getQuestion(
                'Do you want to generate an model class',
                $input->getOption('with-model') ? 'yes' : 'no',
                '?'
            ),
            $input->getOption('with-model')
        );
        $input->setOption('with-model', $withModel);

        // summary
        $output->writeln(
            array(
                '',
                $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
                '',
                sprintf("You are going to generate a \"<info>%s:%s</info>\" Tadcka model", $bundleName, $model),
                sprintf("using the \"<info>%s</info>\" format.", $format),
                '',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createGenerator()
    {
        return new TadckaModelGenerator($this->getContainer()->get('filesystem'));
    }
}
