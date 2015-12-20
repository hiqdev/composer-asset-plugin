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
    /**
     * {@inheritdoc}
     */
    protected $name = 'npm';

    /**
     * {@inheritdoc}
     */
    public $file = 'package.json';

    /**
     * {@inheritdoc}
     */
    public $phpPackage = 'non existent npmphp';

    /**
     * Minimal npm config.
     */
    protected $config = [
        'name'        => 'composer-asset-plugin',
        'description' => "This file is auto-generated with 'hiqdev/composer-asset-plugin'.",
        'readme'      => ' ',
        'repository'  => ['type' => 'git'],
    ];
}
