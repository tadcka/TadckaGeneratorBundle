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
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 2:44 PM
 */
abstract class GenerateTadckaCommand extends GeneratorCommand
{
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
