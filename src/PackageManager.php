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

use Composer\Package\CompletePackage;

/**
 * Abstract package manager class.
 */
abstract class PackageManager
{
    /**
     * The plugin.
     * @var Plugin
     */
    protected $plugin;

    protected $config = [];

    protected $name;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    public function scanPackage(CompletePackage $package)
    {
        $extra = $package->getExtra();
        $config = [];
        foreach (['require', 'require-dev'] as $key) {
            $name = $this->name . '-' . $key;
            if (isset($extra[$name])) {
                $config[$key] = $extra[$name];
            }
        }
        if ($config) {
            $this->mergeConfig($config);
        }
    }

    protected function mergeConfig($config)
    {
    }

    public function loadPackages(array $config)
    {
        $this->config = $config;
    }

    abstract public function installPackages();
}
