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
    /**
     * @inheritdoc
     */
    protected $name = 'bower';

    /**
     * @inheritdoc
     */
    public $file = 'bower.json';

    /**
     * @inheritdoc
     */
    public $phpPackage = 'beelab/bowerphp';

    /**
     * @inheritdoc
     */
    public $phpBin = 'bowerphp';

    /**
     * Minimal bower config.
     */
    protected $config = [
        'name'        => 'composer-asset-plugin',
        'description' => "This file is auto-generated with 'hiqdev/composer-asset-plugin'.",
    ];
}
