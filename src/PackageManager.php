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

use Composer\Json\JsonFile;
use Composer\Package\CompletePackage;

/**
 * Abstract package manager class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
abstract class PackageManager
{
    /**
     * The plugin.
     * @var Plugin
     */
    protected $plugin;

    protected $config = [];

    protected $opTable = [
        'require'     => 'dependencies',
        'require-dev' => 'devDependencies',
    ];

    protected $file;

    protected $name;

    protected $jsonFile;

    /**
     * Reads config file or dist config if exists.
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $dist = $this->file . '.dist';
        $this->config = $this->readConfig(file_exists($dist) ? $dist : $this->file);
    }

    public function readConfig($file)
    {
        $jsonFile = new JsonFile($file);
        $config = $jsonFile->exists() ? $jsonFile->read() : [];
        foreach ($this->opTable as $key) {
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
        }
        return $config;
    }

    public function writeConfig($file, $config)
    {
        foreach ($this->opTable as $key) {
            if (isset($config[$key]) && !$config[$key]) {
                unset($config[$key]);
            }
        }
        $jsonFile = new JsonFile($file);
        $jsonFile->write($config);
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
        foreach ($config as $key => $packages) {
            $key = $this->opTable[$key];
            foreach ($packages as $name => $version) {
                $this->config[$key][$name] = isset($this->config[$key][$name])
                    ? $this->mergeVersions($this->config[$key][$name], $version)
                    : $version;
            }
        }
    }

    protected function mergeVersions($a, $b)
    {
        $a = trim($a);
        $b = trim($b);
        if ($a === $b || $this->isMoreVersion($b, $a)) {
            return $a;
        } elseif ($this->isMoreVersion($a, $b)) {
            return $b;
        } else {
            return $a . ' ' . $b;
        }
    }

    /**
     * Check if $a is more then $b, like: a="1.1 || 2.2" b="1.1"
     * Possible optimization.
     */
    public function isMoreVersion($a, $b)
    {
        return $this->isAnyVersion($a);
        // WRONG: return strpos($b, $a) !== false;
    }

    public function isAnyVersion($version)
    {
        return !$version || $version === '*' || $version === '>=0.0.0';
    }

    public function loadPackages(array $config)
    {
        $this->config = $config;
    }

    abstract public function installPackages();
}
