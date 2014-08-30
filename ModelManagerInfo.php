<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 4:42 PM
 */
class ModelManagerInfo
{
    protected static $drivers = array(
        'doctrine' => array(
            'orm' => 'EntityManager',
            'mongodb' => 'MongoDBDocumentManager'
        ),
    );

    /**
     * Check if is doctrine manager;
     *
     * @param string $dbDriver
     *
     * @return bool
     */
    public static function isDoctrineManager($dbDriver)
    {
        $drivers = self::getDoctrineManagerDrivers();

        return isset($drivers[$dbDriver]);
    }

    /**
     * Get doctrine manager types.
     *
     * @return array
     */
    public static function getDoctrineManagerDrivers()
    {
        return self::$drivers['doctrine'];
    }
}
