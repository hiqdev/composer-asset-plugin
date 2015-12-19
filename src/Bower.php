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
 * Bower package manager class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Bower extends PackageManager
{
    protected $name = 'bower';

    protected $file = 'bower.json';

    public function installPackages()
    {
        $this->plugin->io->write('installing bower dependencies...');
        $this->writeConfig($this->file, $this->config);
    }
}
