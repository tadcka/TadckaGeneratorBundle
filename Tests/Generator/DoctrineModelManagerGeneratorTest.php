<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Tests\Generator;

use Tadcka\Bundle\GeneratorBundle\Generator\DoctrineModelManagerGenerator;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 4:17 PM
 */
class DoctrineModelManagerGeneratorTest extends GeneratorTest
{
    public function testGenerateORM()
    {
        $this->generate('orm');

        $files = array(
            'Doctrine/EntityManager/FooManager.php',
        );

        $otherStrings = array(

            'use Doctrine\\ORM\\EntityManager;',
            'use Doctrine\\ORM\\EntityRepository;',
            'protected $em',
            'public function __construct(EntityManager $em, $class)'
        );

        $this->assertFilesExists($files);
        $this->assertModelManagerAttributesAndMethodsExists('EntityManager', $otherStrings);
    }

    public function testGenerateMongoDb()
    {
        $this->generate('mongodb');

        $files = array(
            'Doctrine/MongoDBDocumentManager/FooManager.php',
        );

        $otherStrings = array(

            'use Doctrine\\ODM\\MongoDB\\DocumentManager;',
            'use Doctrine\\ODM\\MongoDB\\DocumentRepository;',
            'protected $om',
            'public function __construct(DocumentManager $om, $class)'
        );

        $this->assertFilesExists($files);
        $this->assertModelManagerAttributesAndMethodsExists('MongoDBDocumentManager', $otherStrings);
    }

    protected function assertModelManagerAttributesAndMethodsExists($managerDirName, array $otherStrings = array())
    {
        $content = file_get_contents($this->tmpDir.'/Doctrine/' . $managerDirName . '/FooManager.php');

        $strings = array(
            'namespace Foo\\BarBundle\\Doctrine\\' . $managerDirName,
            'use Foo\\BarBundle\\Model\\FooInterface;',
            'use Foo\\BarBundle\\Model\\Manager\\FooManager as BaseFooManager;',
            'class FooManager extends BaseFooManager',
            'protected $repository',
            'protected $class',
            'public function add(FooInterface $value, $save = false)',
            'public function remove(FooInterface $value, $save = false)',
            'public function save()',
            'public function clear()',
            'public function getClass()',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function generate($dbDriver)
    {
        $this->getGenerator()->generate($this->getBundle(), 'Foo', $dbDriver);
    }

    protected function getGenerator()
    {
        $generator = new DoctrineModelManagerGenerator($this->filesystem);
        $generator->setSkeletonDirs(__DIR__ . '/../../Resources/skeleton');

        return $generator;
    }
}
