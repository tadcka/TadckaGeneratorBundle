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

use Sensio\Bundle\GeneratorBundle\Command\GeneratorCommand;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Tadcka\Bundle\GeneratorBundle\ModelManagerInfo;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 2:44 PM
 */
abstract class GenerateTadckaCommand extends GeneratorCommand
{
    protected function addDbDriver(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $dbDrivers = array_keys(ModelManagerInfo::getDoctrineManagerDrivers());
        $dbDriverOption = $input->getOption('db-driver');
        $dbDriverValidator = array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateDbDriver');
        $dbDriverQuestion = $dialog->getQuestion('Configuration db driver (orm, mongodb)', $dbDriverOption);

        return $dialog->askAndValidate($output, $dbDriverQuestion, $dbDriverValidator, false, $dbDriverOption, $dbDrivers);
    }

    protected function addFormat(InputInterface $input, OutputInterface $output, DialogHelper $dialog)
    {
        $formats = array('yml', 'xml', 'php');
        $formatOption = $input->getOption('format');
        $formatValidator = array('Tadcka\Bundle\GeneratorBundle\Command\Validators', 'validateFormat');
        $formatQuestion = $dialog->getQuestion('Configuration format (yml, xml, or php)', $formatOption);

        return $dialog->askAndValidate($output, $formatQuestion, $formatValidator, false, $formatOption, $formats);
    }

    protected function parseShortcutNotation($shortcut)
    {
        $model = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($model, ':')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The model name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)',
                    $model
                )
            );
        }

        return array(substr($model, 0, $pos), substr($model, $pos + 1));
    }

    /**
     * {@inheritdoc}
     */
    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath() . '/Resources/TadckaGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $dir = $this->getContainer()->get('kernel')->getRootdir() . '/Resources/TadckaGeneratorBundle/skeleton';
        if (is_dir($dir)) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
        $skeletonDirs[] = __DIR__ . '/../Resources';

        return $skeletonDirs;
    }
}
