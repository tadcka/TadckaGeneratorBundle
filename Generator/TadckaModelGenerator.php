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
     * @var string
     */
    private $kernelRootDir;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem
     * @param string $kernelRootDir
     */
    public function __construct(Filesystem $filesystem, $kernelRootDir)
    {
        $this->filesystem = $filesystem;
        $this->kernelRootDir = $kernelRootDir;
    }

    /**
     * Generate model and model interface.
     *
     * @param BundleInterface $bundle
     * @param string $model
     * @param array $fields
     * @param bool $withManager
     * @param string $dbDriver
     * @param string $format
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $model, array $fields, $withManager, $dbDriver, $format)
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

        $uses = array();
        foreach ($fields as &$field) {
            $field['is_argument_type'] = false;
            if ((class_exists($field['type']) || interface_exists($field['type'])) && '\DateTime' !== $field['type']) {
                $uses[] = ltrim($field['type'], '\\');
                $namespace = explode('\\', $field['type']);
                $field['type'] = end($namespace);
                $field['is_argument_type'] = true;
            } elseif ('\DateTime' === $field['type']) {
                $field['is_argument_type'] = true;
            }
        }

        $parameters = array(
            'namespace' => $bundle->getNamespace(),
            'fields' => $fields,
            'model_name' => $model,
            'uses' => $uses,
        );

        $this->renderFile('model/ModelInterface.php.twig', $modelInterfaceFile, $parameters);
        $this->renderFile('model/Model.php.twig', $modelFile, $parameters);

        if (true === $withManager) {
            $managerGenerator = new TadckaModelManagerGenerator($this->filesystem);
            $skeletonDirs = $this->getSkeletonDirs($bundle);
            $managerGenerator->setSkeletonDirs($skeletonDirs);
            $managerGenerator->generate($bundle, $model);

            if (ModelManagerInfo::isDoctrineManager($dbDriver)) {
                $doctrineManagerGenerator = new DoctrineModelManagerGenerator($this->filesystem);
                $doctrineManagerGenerator->setSkeletonDirs($skeletonDirs);
                $doctrineManagerGenerator->generate($bundle, $model, $dbDriver, $format);
            }
        }
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

        $dir = $this->kernelRootDir . '/Resources/TadckaGeneratorBundle/skeleton';
        if (is_dir($dir)) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = __DIR__ . '/../Resources/skeleton';
        $skeletonDirs[] = __DIR__ . '/../Resources';

        return $skeletonDirs;
    }
}
