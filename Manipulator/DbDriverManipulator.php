<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Manipulator;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Tadcka\Bundle\GeneratorBundle\ModelManagerInfo;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/31/14 2:56 PM
 */
class DbDriverManipulator
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * Constructor.
     *
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Add resource.
     *
     * @param BundleInterface $bundle
     * @param $modelName
     * @param $dbDriver
     *
     * @return bool
     *
     * @throws \RuntimeException
     */
    public function addResource(BundleInterface $bundle, $modelName, $dbDriver)
    {
        if (false === file_exists($this->filePath)) {
            throw new \RuntimeException(sprintf('Db driver configuration file "%s" not exists', $this->filePath));
        }

        $fileContent = file_get_contents($this->filePath);
        $bundleAlias = Container::underscore(substr($bundle->getName(), 0, -6));
        $modelAlias = Container::underscore($modelName);
        $modelManagerClass = $bundleAlias . '.manager.' . $modelAlias . '.class';
        $modelClass = $bundleAlias . '.model.' . $modelAlias . '.class';
        $modelManagerId = $bundleAlias . '.manager.' . $modelAlias . '.default';

        if ((false !== strpos($fileContent, $modelManagerClass)) || (false !== strpos($fileContent, $modelManagerId))) {
            throw new \RuntimeException(sprintf('Model manager configuration "%s" is already imported.', $modelManagerClass));
        }

        $modelManagerFile = $bundle->getNamespace() . '\\' . ModelManagerInfo::getManagerFolderName($dbDriver) . '\\' . $modelName . 'Manager';
        $parameter = sprintf("        <parameter key=\"%s\">%s</parameter>\n", $modelManagerClass, $modelManagerFile);
        $parameter .= sprintf("    </parameters>");

        $fileContent = str_replace("    </parameters>", $parameter, $fileContent);

        $service = sprintf("        <!--Default %s manager-->\n", $modelName);
        $service .= sprintf("        <service id=\"%s\" class=\"%%%s%%\">\n", $modelManagerId, $modelManagerClass);

        if ('orm' === $dbDriver) {
            $service .= sprintf("            <argument type=\"service\" id=\"doctrine.orm.entity_manager\" />\n");
        } elseif ('mongodb' === $dbDriver) {
            $service .= sprintf("            <argument type=\"service\" id=\"doctrine.odm.mongodb.document_manager\" />\n");
        }


        $service .= sprintf("            <argument>%%%s%%</argument>\n", $modelClass);
        $service .= sprintf("        </service>\n\n");
        $service .= sprintf("    </services>");

        $fileContent = str_replace("    </services>", $service, $fileContent);

        if (false === file_put_contents($this->filePath, $fileContent)) {
            return false;
        }

        return true;
    }
}
