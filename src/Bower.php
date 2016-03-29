<?php

/*
 * Composer plugin for bower/npm assets
 *
 * @link      https://github.com/hiqdev/composer-asset-plugin
 * @package   composer-asset-plugin
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015-2016, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\composerassetplugin;

/**
 * Bower package manager class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Bower extends PackageManager
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'bower';

    /**
     * {@inheritdoc}
     */
    public $file = 'bower.json';

    /**
     * {@inheritdoc}
     */
    public $rcfile = '.bowerrc';

    /**
     * {@inheritdoc}
     */
    public $phpPackage = 'beelab/bowerphp';

    /**
     * {@inheritdoc}
     */
    public $phpBin = 'bowerphp';

    /**
     * @var array Minimal bower config
     */
    protected $config = [
        'name'        => 'composer-asset-plugin',
        'description' => "This file is auto-generated with 'hiqdev/composer-asset-plugin'.",
    ];

    public function setDestination($dir)
    {
        if (substr($dir, 0, 7) === 'vendor/') {
            $dir = substr($dir, 7);
        }
        $this->rc['directory'] = $dir;
    }

    public function writeRc($path, $data) {
        $this->writeJson($path, $data);
    }

    public function fixConstraint($constraint)
    {
        if (Constraint::isDisjunctive($constraint)) {
            $constraint = Constraint::findMax(explode('|', $constraint));
        }

        $pos = strpos($constraint, '@');
        if ($pos !== false) {
            $constraint = substr($constraint, 0, $pos);
        }

        return $constraint;
    }
}
