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
use Tadcka\Bundle\GeneratorBundle\ModelManagerInfo;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 12:36 PM
 */
class DoctrineModelManagerGenerator extends Generator
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
     * Generate doctrine model manager.
     *
     * @param BundleInterface $bundle
     * @param string $model
     * @param string $dbDriver
     * @param string $format
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function generate(BundleInterface $bundle, $model, $dbDriver, $format)
    {
        if (false === ModelManagerInfo::isDoctrineManager($dbDriver)) {
            throw new \InvalidArgumentException(sprintf('Not found db driver "%s"', $dbDriver));
        }

        $dir = $bundle->getPath();

        $managerInterface = $dir . '/Model/Manager/' . $model . 'ManagerInterface.php';
        if (false === $this->filesystem->exists($managerInterface)) {
            throw new \RuntimeException(sprintf('Model manager interface "%s" not exists', $managerInterface));
        }

        $managerDrivers = ModelManagerInfo::getDoctrineManagerDrivers();

        $managerFile = $dir . '/Doctrine/' . $managerDrivers[$dbDriver] . '/' . $model . 'Manager.php';

        if ($this->filesystem->exists($managerFile)) {
            throw new \RuntimeException(sprintf('Doctrine model manager "%s" already exists', $managerFile));
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'model_name' => $model,
            'value' => lcfirst($model),
        );

        $this->renderFile(
            'model/doctrine/manager/' . $managerDrivers[$dbDriver] . '.php.twig',
            $managerFile,
            $parameters
        );
    }
}
