<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Tests\Manipulator;

use Tadcka\Bundle\GeneratorBundle\Manipulator\DbDriverManipulator;
use Tadcka\Bundle\GeneratorBundle\Tests\GeneratorTest;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/31/14 3:05 PM
 */
class DbDriverManipulatorTest extends GeneratorTest
{
    CONST MONGO_DB = 'mongodb';

    /**
     * @expectedException \RuntimeException
     */
    public function testAddResourceConfigurationFileNotExists()
    {
        $this->addResource($this->getConfigurationFilePath('orm', 'xml'), 'Test', 'orm');
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAddResourceConfigurationAlreadyImported()
    {
        $this->addXmlConfigurationFile('doctrine.odm.mongodb.document_manager', 'orm');

        $this->addResource($this->getConfigurationFilePath('orm', 'xml'), 'Test', 'orm');
    }

    public function testAddOrmResource()
    {
        $this->addXmlConfigurationFile('doctrine.odm.mongodb.document_manager', 'orm');

        $this->assertTrue($this->addResource($this->getConfigurationFilePath('orm', 'xml'), 'Baz', 'orm'));

        $otherStrings = array(
            '<parameter key="foo_bar.manager.baz.class">Foo\BarBundle\Doctrine\EntityManager\BazManager</parameter>',
            '<argument type="service" id="doctrine.orm.entity_manager" />'
        );

        $this->assertParameterAndServiceExists('orm', 'xml', $otherStrings);
    }

    public function testAddMongodbResource()
    {
        $this->addXmlConfigurationFile('doctrine.odm.mongodb.document_manager', self::MONGO_DB);

        $this->assertTrue(
            $this->addResource($this->getConfigurationFilePath(self::MONGO_DB, 'xml'), 'Baz', self::MONGO_DB)
        );

        $otherStrings = array(
            '<parameter key="foo_bar.manager.baz.class">Foo\BarBundle\Doctrine\MongoDBDocumentManager\BazManager</parameter>',
            '<argument type="service" id="doctrine.odm.mongodb.document_manager" />'
        );

        $this->assertParameterAndServiceExists(self::MONGO_DB, 'xml', $otherStrings);
    }

    protected function assertParameterAndServiceExists($dbDriver, $format, array $otherStrings = array())
    {
        $content = file_get_contents($this->getConfigurationFilePath($dbDriver, $format));

        $strings = array(
            '<!--Default Baz manager-->',
            '<service id="foo_bar.manager.baz.default" class="%foo_bar.manager.baz.class%">',
            '<argument>%foo_bar.model.baz.class%</argument>',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function addResource($filePath, $model, $dbDriver)
    {
        return $this->getManipulator($filePath)->addResource($this->getBundle(), $model, $dbDriver);
    }

    protected function getManipulator($filePath)
    {
        return new DbDriverManipulator($filePath);
    }

    protected function addXmlConfigurationFile($service, $dbDriver)
    {
        $fileContent = <<<EOF
<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">

    <parameters>
        <parameter key="foo_bar.manager.test.class">Foo\BarBundle\Doctrine\EntityManager\TestManager</parameter>
    </parameters>

    <services>

        <!--Default Test manager-->
        <service id="foo_bar.manager.test.default" class="%foo_bar.manager.test.class%">
            <argument type="service" id="$service" />
            <argument>%test.model.product_route.class%</argument>
        </service>

    </services>
</container>
EOF;

        $this->filesystem->dumpFile($this->getConfigurationFilePath($dbDriver, 'xml'), $fileContent);
    }

    protected function getConfigurationFilePath($dbDriver, $format)
    {
        return $this->tmpDir . '\\Foo\\BarBundle\\Resources\\config\\db_driver\\' . $dbDriver . '.' . $format;
    }


    /**
     * @return string
     */
    protected function getMockFilesDir()
    {
        return dirname(__FILE__) . '/../MockFiles/';
    }
}
