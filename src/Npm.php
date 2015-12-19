<?php

/*
 * Composer plugin for bower/npm assets
 *
 * @link      https://github.com/hiqdev/composer-asset-plugin
 * @package   composer-asset-plugin
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\composerassetplugin;

/**
 * NPM package manager class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Npm extends PackageManager
{
    protected $name = 'npm';

    protected $file = 'package.json';

    public function installPackages()
    {
    }
}
