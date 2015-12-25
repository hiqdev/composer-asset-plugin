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
     * @var Plugin the plugin instance
     */
    protected $plugin;

    /**
     * @var string Package manager name: `bower` or `npm`
     */
    protected $name;

    /**
     * @var string Package config file name: `bower.json` or `package.json`
     */
    public $file;

    /**
     * @var string Path to package manager binary
     */
    public $bin;

    /**
     * @var string Package name of the PHP version of the package manager
     */
    public $phpPackage;

    /**
     * @var string Binary name of PHP version of package manager
     */
    protected $phpBin;

    /**
     * @var array Package config. Initially holds default config
     */
    protected $config = [];

    /**
     * @var array List of keys holding dependencies
     */
    protected $dependencies = ['dependencies', 'devDependencies'];

    /**
     * Reads config file or dist config if exists, merges with default config.
     * @param Plugin $plugin
     * @void
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
        //$dist = $this->file . '.dist';
        $this->config = array_merge(
            $this->config,
            $this->readConfig($this->file)
            //$this->readConfig(file_exists($dist) ? $dist : $this->file)
        );
    }

    /**
     * Reads the JSON config from the $path.
     *
     * @param string $path path to the Json file
     * @return array|mixed
     */
    public function readConfig($path)
    {
        $jsonFile = new JsonFile($path);
        $config = $jsonFile->exists() ? $jsonFile->read() : [];
        foreach ($this->dependencies as $key) {
            if (!isset($config[$key])) {
                $config[$key] = [];
            }
        }
        return $config;
    }

    /**
     * Saves JSON config to the given path.
     *
     * @param string $path
     * @param array $config
     * @throws \Exception
     */
    public function writeConfig($path, array $config)
    {
        foreach ($this->dependencies as $key) {
            if (isset($config[$key]) && !$config[$key]) {
                unset($config[$key]);
            }
        }
        $jsonFile = new JsonFile($path);
        $jsonFile->write($config);
    }

    /**
     * Scans the $package and extracts dependencies to the [[config]].
     *
     * @param CompletePackage $package
     * @see mergeConfig()
     * @void
     */
    public function scanPackage(CompletePackage $package)
    {
        $extra = $package->getExtra();
        $config = [];
        foreach ($this->dependencies as $key) {
            $name = $this->name . '-' . $key;
            if (isset($extra[$name])) {
                $config[$key] = $extra[$name];
            }
        }
        if (!empty($config)) {
            $this->mergeConfig($config);
        }
    }

    /**
     * Merges the $config over the [[config]], doesn't resolve version conflicts.
     * @param array $config
     * @see mergeVersions()
     * @void
     */
    protected function mergeConfig(array $config)
    {
        foreach ($config as $key => $packages) {
            foreach ($packages as $name => $version) {
                if (isset($this->config[$key][$name])) {
                    $this->config[$key][$name] = $this->mergeVersions($this->config[$key][$name], $version);
                } else {
                    $this->config[$key][$name] = $version;
                }
            }
        }
    }

    /**
     * @param $a
     * @param $b
     * @return string
     */
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
     * // TODO Rename and implement.
     * @param string $a
     * @param string $b
     * @return boolean
     */
    public function isMoreVersion($a, $b)
    {
        return $this->isAnyVersion($a);
    }

    /**
     * Checks whether the $version represents any possible version.
     *
     * @param string $version
     * @return boolean
     */
    public function isAnyVersion($version)
    {
        return $version === '' || $version === '*' || $version === '>=0.0.0';
    }

    /**
     * Set config.
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * Run the given action: show notice, write config and run `perform`.
     * @param string $action the action name
     * @void
     */
    public function runAction($action)
    {
        $doing = trim($action, 'e') . 'ing';
        $this->plugin->io->write($doing . ' ' . $this->name . ' dependencies...');
        $this->writeConfig($this->file, $this->config);
        $this->perform($action);
    }

    /**
     * Run installation. Specific for every package manager.
     * @param string $action the action name
     * @void
     */
    protected function perform($action)
    {
        if ($this->passthru([$action])) {
            $this->plugin->io->writeError('failed ' . $this->name . ' ' . $action);
        }
    }

    /**
     * Prepares arguments and runs the command with [[passthru()]].
     * @param array $arguments
     * @return integer the exit code
     */
    public function passthru(array $arguments = [])
    {
        passthru($this->getBin() . $this->prepareCommand($arguments), $exitCode);
        return $exitCode;
    }

    /**
     * Prepares given command arguments.
     * @param array $arguments
     * @return string
     */
    public function prepareCommand(array $arguments = [])
    {
        $result = '';
        foreach ($arguments as $a) {
            $result .= ' ' . escapeshellarg($a);
        }

        return $result;
    }

    /**
     * Set path to binary executable file.
     * @param $bin
     * @internal param string $value
     */
    public function setBin($bin)
    {
        $this->bin = $bin;
    }

    /**
     * Get path to the binary executable file.
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
     * Find path to the binary.
     * @return string
     */
    public function detectBin()
    {
        if (isset($this->plugin->getPackages()[$this->phpPackage])) {
            return $this->plugin->getVendorDir() . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $this->phpBin;
        }

        return $this->name;
    }
}
