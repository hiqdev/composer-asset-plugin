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

    /**
     * Package manager name: bower or npm.
     * @var string
     */
    protected $name;

    /**
     * Package config file: bower.json or package.json
     * @var string
     */
    public $file;

    /**
     * Path to package manager binary.
     * @var string
     */
    public $bin;

    /**
     * Package name of PHP version of package manager.
     * @var string
     */
    public $phpPackage;

    /**
     * Binary name of PHP version of package manager.
     * @var string
     */
    protected $phpBin;

    /**
     * Package config. Initially holds default config.
     */
    protected $config = [];

    /**
     * Conversion table.
     * @var string
     */
    protected $keyTable = [
        'require'     => 'dependencies',
        'require-dev' => 'devDependencies',
    ];

    /**
     * Reads config file or dist config if exists, merges with default config.
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        $dist = $this->file . '.dist';
        $this->config = array_merge(
            $this->config,
            $this->readConfig(file_exists($dist) ? $dist : $this->file)
        );
    }

    public function readConfig($file)
    {
        $jsonFile = new JsonFile($file);
        $config = $jsonFile->exists() ? $jsonFile->read() : [];
        foreach ($this->keyTable as $key) {
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
        }
        return $config;
    }

    public function writeConfig($file, $config)
    {
        foreach ($this->keyTable as $key) {
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
            $key = $this->keyTable[$key];
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

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Install packages.
     */
    public function installPackages()
    {
        $this->plugin->io->write('installing ' . $this->name . ' dependencies...');
        $this->writeConfig($this->file, $this->config);
        $this->runInstall();
    }

    /**
     * Run installation. Specific for every package manager.
     */
    abstract protected function runInstall();

    /**
     * Prepares given command arguments.
     * @param string|array $args
     * @return string
     */
    public function prepareCommand($args = '')
    {
        if (is_string($args)) {
            $res = ' ' . trim($args);
        } else {
            $res = '';
            foreach ($args as $a) {
                $res .= ' ' . escapeshellarg($a);
            }
        }

        return $res;
    }

    /**
     * Prepares arguments and runs it with passthru.
     * @param string $args
     * @return int exit code
     */
    public function passthru($args = '')
    {
        passthru($this->getBin() . $this->prepareCommand($args), $exitcode);
        return $exitcode;
    }

    /**
     * Set path to binary.
     * @param string $value
     */
    public function setBin($value)
    {
        $this->bin = $value;
    }

    /**
     * Get path to binary.
     * @return string
     */
    public function getBin()
    {
        if ($this->bin === null) {
            $this->bin = $this->detectBin();
        }

        return $this->bin;
    }

    /**
     * Find path binary.
     * @return string
     */
    public function detectBin()
    {
        if (isset($this->plugin->getPackages()[$this->phpPackage])) {
            return './vendor/bin/' . $this->phpBin;
        }

        return $this->name;
    }
}
