<?php

/*
 * This file is part of the Tadcka package.
 *
 * (c) Tadcka <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tadcka\Bundle\GeneratorBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Validators as BaseValidators;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 8/30/14 2:39 PM
 */
class Validators extends BaseValidators
{
    /**
     * Validate model name.
     *
     * @param string $model
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function validateModelName($model)
    {
        if (false === strpos($model, ':')) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The model name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)',
                    $model
                )
            );
        }

        return $model;
    }

    public static function validateDbDriver($dbDriver)
    {
        $dbDriver = strtolower($dbDriver);

        if (!in_array($dbDriver, array('orm', 'mongodb'))) {
            throw new \RuntimeException(sprintf('Db driver "%s" is not supported.', $dbDriver));
        }

        return $dbDriver;
    }
}
