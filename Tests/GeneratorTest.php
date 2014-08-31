<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Tests;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/31/14 4:03 PM
 */
abstract class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $tmpDir;

    protected function setUp()
    {
        $this->tmpDir = sys_get_temp_dir() . '/sf2';
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->tmpDir);
    }

    protected function tearDown()
    {
        $this->filesystem->remove($this->tmpDir);
    }

    protected function getBundle()
    {
        $bundle = $this->getMock('Symfony\Component\HttpKernel\Bundle\BundleInterface');
        $bundle->expects($this->any())->method('getPath')->will($this->returnValue($this->tmpDir));
        $bundle->expects($this->any())->method('getName')->will($this->returnValue('FooBarBundle'));
        $bundle->expects($this->any())->method('getNamespace')->will($this->returnValue('Foo\BarBundle'));

        return $bundle;
    }

    protected function assertFilesExists(array $files)
    {
        foreach ($files as $file) {
            $this->assertTrue(file_exists($this->tmpDir.'/'.$file), sprintf('%s has been generated', $file));
        }
    }
}
