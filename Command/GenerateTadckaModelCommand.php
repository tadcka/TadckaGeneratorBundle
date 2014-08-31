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
use Symfony\Component\DependencyInjection\Container;
use Tadcka\Bundle\GeneratorBundle\Generator\TadckaModelGenerator;
use Tadcka\Bundle\GeneratorBundle\ModelManagerInfo;

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
            ->addOption('with-manager', null, InputOption::VALUE_NONE, 'Whether to generate the model manager or not')
            ->addOption('db-driver', null, InputOption::VALUE_REQUIRED, 'Set model manager db driver (orm, mongodb)', 'orm')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Use the format for configuration files (php, xml, or yml)', 'xml')
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

        $model = Validators::validateModelName($input->getOption('model'));
        list($bundle, $model) = $this->parseShortcutNotation($model);
        $fields = $this->parseFields($input->getOption('fields'));
        $withManager = $input->getOption('with-manager');
        $dbDriver = Validators::validateDbDriver($input->getOption('db-driver'));
        $format = Validators::validateFormat($input->getOption('format'));

        $dialog->writeSection($output, 'Model generation');

        $bundle = $this->getContainer()->get('kernel')->getBundle($bundle);

        $generator = $this->getGenerator();
        $generator->generate($bundle, $model, array_values($fields), $withManager, $dbDriver, $format);

        $output->writeln('Generating the model code: <info>OK</info>');

        $dialog->writeGeneratorSummary($output, array());
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
        $modelInfo = $this->addModel($input, $output, $dialog);
        $input->setOption('model', $modelInfo['bundle_name'] . ':' . $modelInfo['model']);

        // fields
        $input->setOption('fields', $this->addFields($input, $output, $dialog));

        // manager?
        $output->writeln('');
        $withManager = $this->addWithManager($input, $output, $dialog);
        $input->setOption('with-manager', $withManager);

        $summary = array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf("You are going to generate a \"<info>%s:%s</info>\" Tadcka model", $modelInfo['bundle_name'], $modelInfo['model']),
            '',
        );

        if ($withManager) {
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

            $summary[] = sprintf("using the \"<info>%s</info>\" db driver and \"<info>%s</info>\" format.", $dbDriver, $format);
            $summary[] = '';
        }

        // summary
        $output->writeln($summary);
    }

    private function addDbDriver(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $dbDrivers = array_keys(ModelManagerInfo::getDoctrineManagerDrivers());
        $dbDriverOption = $input->getOption('db-driver');
        $dbDriverValidator = array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateDbDriver');
        $dbDriverQuestion = $dialog->getQuestion('Configuration db driver (orm, mongodb)', $dbDriverOption);

        return $dialog->askAndValidate($output, $dbDriverQuestion, $dbDriverValidator, false, $dbDriverOption, $dbDrivers);
    }

    private function addWithManager(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $withManagerOption = $input->getOption('with-manager');
        $withManagerQuestion = $dialog->getQuestion('Do you want to generate an model manager class', $withManagerOption ? 'yes' : 'no', '?');

        return $dialog->askConfirmation($output, $withManagerQuestion, $withManagerOption);
    }

    private function addFormat(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $formats = array('yml', 'xml', 'php');
        $formatOption = $input->getOption('format');
        $formatValidator = array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateFormat');
        $formatQuestion = $dialog->getQuestion('Configuration format (yml, xml, or php)', $formatOption);

        return $dialog->askAndValidate($output, $formatQuestion, $formatValidator, false, $formatOption, $formats);
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

                if (!file_exists($bundle->getPath() . '/Model/' . str_replace('\\', '/', $model) . '.php')) {
                    break;
                }

                $output->writeln(sprintf('<bg=red>Model "%s:%s" already exists</>.', $bundleName, $model));
            } catch (\Exception $e) {
                $output->writeln(sprintf('<bg=red>Bundle "%s" does not exist.</>', $bundleName));
            }
        }

        return array('bundle_name' => $bundleName, 'model' => $model);
    }

    private function addFields(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $fields = $this->parseFields($input->getOption('fields'));
        $output->writeln(
            array(
                '',
                'Instead of starting with a blank model, you can add some fields now.',
                '',
                '',
            )
        );
        $output->write('<info>Available types:</info> ');

        $types = array('string', 'int', 'float', 'bool', 'array', '\DateTime');
        $count = 20;
        foreach ($types as $i => $type) {
            if ($count > 50) {
                $count = 0;
                $output->writeln('');
            }
            $count += strlen($type);
            $output->write(sprintf('<comment>%s</comment>', $type));
            if (count($types) != $i + 1) {
                $output->write(', ');
            } else {
                $output->write(' and other objects.');
            }
        }
        $output->writeln('');

        $fieldValidator = function ($type) use ($types) {
            if (!in_array($type, $types) && !class_exists($type) && !interface_exists($type)) {
                throw new \InvalidArgumentException(sprintf('Invalid type "%s".', $type));
            }

            return $type;
        };

        $fieldNameValidator = function ($name) use ($fields) {
            if (isset($fields[$name])) {
                throw new \InvalidArgumentException(sprintf('Field "%s" is already defined.', $name));
            }

            return $name;
        };

        while (true) {
            $output->writeln('');

            $fieldNameQuestion = $dialog->getQuestion('New field name (press <return> to stop adding fields)', null);
            $fieldName = $dialog->askAndValidate($output, $fieldNameQuestion, $fieldNameValidator);

            if (!$fieldName) {
                break;
            }

            $fieldTypeQuestion = $dialog->getQuestion('Field type', 'string');
            $type = $dialog->askAndValidate($output, $fieldTypeQuestion, $fieldValidator, false, 'string', $types);

            $fields[$fieldName] = array('name' => lcfirst(Container::camelize($fieldName)), 'type' => $type);
        }

        return $fields;
    }

    private function parseFields($input)
    {
        if (is_array($input)) {
            return $input;
        }

        $fields = array();
        foreach (explode(' ', $input) as $value) {
            $elements = explode(':', $value);
            $name = $elements[0];
            if (strlen($name)) {
                $type = isset($elements[1]) ? $elements[1] : 'string';
                preg_match_all('/(.*)\((.*)\)/', $type, $matches);
                $type = isset($matches[1][0]) ? $matches[1][0] : $type;

                $fields[$name] = array('name' => $name, 'type' => $type);
            }
        }

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function createGenerator()
    {
        return new TadckaModelGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->get('kernel')->getRootdir()
        );
    }
}
