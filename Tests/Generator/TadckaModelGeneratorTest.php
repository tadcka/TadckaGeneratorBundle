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

use Tadcka\Bundle\GeneratorBundle\Generator\TadckaModelGenerator;
use Tadcka\Bundle\GeneratorBundle\Tests\GeneratorTest;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 2:35 PM
 */
class TadckaModelGeneratorTest extends GeneratorTest
{
    public function testGenerate()
    {
        $this->generate();

        $files = array(
            'Model/FooInterface.php',
            'Model/Foo.php',
        );

        $this->assertFilesExists($files);
        $this->assertModelInterfaceMethodsExists();
        $this->assertModelAttributesAndMethodsExists();
    }

    protected function assertModelAttributesAndMethodsExists(array $otherStrings = array())
    {
        $content = file_get_contents($this->tmpDir . '/Model/Foo.php');

        $strings = array(
            'namespace Foo\\BarBundle\\Model',
            'class Foo implements FooInterface',
            'protected $bar',
            'protected $baz',
            'public function getBar()',
            'public function getBaz()',
            'public function setBar($bar)',
            'public function setBaz($baz)',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function assertModelInterfaceMethodsExists(array $otherStrings = array())
    {
        $content = file_get_contents($this->tmpDir.'/Model/FooInterface.php');

        $strings = array(
            'namespace Foo\\BarBundle\\Model',
            'interface FooInterface',
            'public function getBar();',
            'public function getBaz();',
            'public function setBar($bar);',
            'public function setBaz($baz);',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function generate($withManager = false)
    {
        $this->getGenerator()->generate($this->getBundle(), 'Foo', $this->getFields(), $withManager, null, null);
    }

    protected function getGenerator()
    {
        $generator = new TadckaModelGenerator($this->filesystem, $this->tmpDir);
        $generator->setSkeletonDirs(__DIR__ . '/../../Resources/skeleton');

        return $generator;
    }

    protected function getFields()
    {
        return array(
            array('name' => 'bar', 'type' => 'string'),
            array('name' => 'baz', 'type' => 'int'),
            array('name' => 'acme', 'type' => 'Foo\\BarBundle\\Model\\AcmeInterface'),
        );
    }
}
