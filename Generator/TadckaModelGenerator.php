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
 * @since 8/30/14 12:34 PM
 */
class TadckaModelGenerator extends Generator
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
     * Generate model and model interface.
     *
     * @param BundleInterface $bundle
     * @param string $model
     * @param string $format
     * @param array $fields
     * @param bool $withManager
     * @param string $dbDriver
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $model, $format, array $fields, $withManager, $dbDriver)
    {
        $dir = $bundle->getPath();

        $modelInterfaceFile = $dir . '/Model/' . $model . 'Interface.php';
        if ($this->filesystem->exists($modelInterfaceFile)) {
            throw new \RuntimeException(sprintf('Model interface "%s" already exists', $modelInterfaceFile));
        }

        $modelFile = $dir . '/Model/' . $model . '.php';
        if ($this->filesystem->exists($modelFile)) {
            throw new \RuntimeException(sprintf('Model "%s" already exists', $modelFile));
        }

        $parameters = array(
            'namespace'  => $bundle->getNamespace(),
            'fields' => $fields,
            'model_name' => $model,
        );

        $this->renderFile('model/ModelInterface.php.twig', $modelInterfaceFile, $parameters);
        $this->renderFile('model/Model.php.twig', $modelFile, $parameters);

        if (true === $withManager) {
            $modelManager = new TadckaModelManagerGenerator($this->filesystem);
            $modelManager->generate($bundle, $model);

            if (ModelManagerInfo::isDoctrineManager($dbDriver)) {
                $doctrineModelManager = new DoctrineModelManagerGenerator($this->filesystem);
                $doctrineModelManager->generate($bundle, $model, $dbDriver);
            }
        }
    }
}
