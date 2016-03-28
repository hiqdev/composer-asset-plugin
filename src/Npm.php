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
     * {@inheritdoc}
     * Not really sure if peer, bundled and optional dependencies are really needed.
     * Remove them if not.
     */
    protected $dependencies = [
        'dependencies', 'devDependencies',
        'peerDependencies', 'bundledDependencies', 'optionalDependencies',
    ];

    /**
     * @var array Minimal NPM config
     */
    protected $config = [
        'name'        => 'composer-asset-plugin',
        'description' => "This file is auto-generated with 'hiqdev/composer-asset-plugin'.",
        'readme'      => ' ',
        'repository'  => ['type' => 'git'],
    ];
}
