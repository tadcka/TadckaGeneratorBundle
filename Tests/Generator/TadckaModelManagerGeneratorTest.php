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

use Tadcka\Bundle\GeneratorBundle\Generator\TadckaModelManagerGenerator;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 3:51 PM
 */
class TadckaModelManagerGeneratorTest extends GeneratorTest
{
    public function testGenerate()
    {
        $this->generate();

        $files = array(
            'Model/Manager/FooManagerInterface.php',
            'Model/Manager/FooManager.php',
        );

        $this->assertFilesExists($files);
        $this->assertModelManagerInterfaceMethodsExists();
        $this->assertAbstractModelManagerMethodsExists();
    }

    protected function assertModelManagerInterfaceMethodsExists(array $otherStrings = array())
    {
        $content = file_get_contents($this->tmpDir . '/Model/Manager/FooManagerInterface.php');

        $strings = array(
            'namespace Foo\\BarBundle\\Model\\Manager',
            'interface FooManagerInterface',
            'public function create();',
            'public function add(FooInterface $foo, $save = false);',
            'public function remove(FooInterface $foo, $save = false);',
            'public function save();',
            'public function clear();',
            'public function getClass();',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function assertAbstractModelManagerMethodsExists(array $otherStrings = array())
    {
        $content = file_get_contents($this->tmpDir . '/Model/Manager/FooManager.php');

        $strings = array(
            'namespace Foo\\BarBundle\\Model\\Manager',
            'abstract class FooManager',
            'public function create()',
        );

        $strings = array_merge($strings, $otherStrings);

        foreach ($strings as $string) {
            $this->assertContains($string, $content);
        }
    }

    protected function generate()
    {
        $this->getGenerator()->generate($this->getBundle(), 'Foo');
    }

    protected function getGenerator()
    {
        $generator = new TadckaModelManagerGenerator($this->filesystem);
        $generator->setSkeletonDirs(__DIR__ . '/../../Resources/skeleton');

        return $generator;
    }
}
