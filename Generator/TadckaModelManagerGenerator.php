<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 12:35 PM
 */
class TadckaModelManagerGenerator extends Generator
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Generate model abstract manager and model manager interface.
     *
     * @param BundleInterface $bundle
     * @param string $model
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $model)
    {
        $dir = $bundle->getPath();

        $managerInterfaceFile = $dir . '/Model/Manager/' . $model . 'ManagerInterface.php';
        if ($this->filesystem->exists($managerInterfaceFile)) {
            throw new \RuntimeException(sprintf('Model manager interface "%s" already exists', $managerInterfaceFile));
        }

        $abstractManagerFile = $dir . '/Model/Manager/' . $model . 'Manager.php';
        if ($this->filesystem->exists($abstractManagerFile)) {
            throw new \RuntimeException(sprintf('Abstract model manager "%s" already exists', $abstractManagerFile));
        }

        $parameters = array(
            'namespace'  => $bundle->getNamespace(),
            'model_name' => $model,
        );

        $this->renderFile('model/manager/ModelManagerInterface.php.twig', $managerInterfaceFile, $parameters);
        $this->renderFile('model/manager/ModelManager.php.twig', $abstractManagerFile, $parameters);
    }
}
