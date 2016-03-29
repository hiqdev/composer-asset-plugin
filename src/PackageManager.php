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

use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;

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
     * @var string Package RC config file name: `.bowerrc` or `.npmrc`
     */
    public $rcfile;

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
     * @var array RC config: .bowerrc or .npmrc
     */
    protected $rc = [];

    /**
     * @var array List of keys holding dependencies
     */
    protected $dependencies = ['dependencies', 'devDependencies'];

    /**
     * array known deps collected from requirements.
     */
    protected $knownDeps = [];

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

    public function getName()
    {
        return $this->name;
    }

    public function packageFullName($package)
    {
        return $package->getName() . ':' . $package->getVersion();
    }

    public function setKnownDeps(PackageInterface $package, $type, $name, $constraint)
    {
        $res = $this->getKnownDeps($package);
        if (!isset($res[$type])) {
            $res[$type] = [];
        }
        $res[$type][$name] = $constraint;
        $this->knownDeps[$this->packageFullName($package)] = $res;
    }

    public function getKnownDeps(PackageInterface $package)
    {
        $full = $this->packageFullName($package);
        return isset($this->knownDeps[$full]) ? $this->knownDeps[$full] : [];
    }

    public function getConfig()
    {
        return $this->config;
    }

    abstract public function setDestination($dir);
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
    public function writeJson($path, array $config)
    {
        $jsonFile = new JsonFile($path);
        $jsonFile->write($this->prepareConfig($config));
    }

    public function prepareConfig(array $config)
    {
        foreach ($this->dependencies as $key) {
            if (!isset($config[$key])) {
                continue;
            }
            if (!$config[$key]) {
                unset($config[$key]);
                continue;
            }
            foreach ($config[$key] as $name => &$constraint) {
                $constraint = $this->fixConstraint($constraint);
            }
        }

        return $config;
    }

    /**
     * Fixes constraint for the package manager.
     * Does nothing for NPM. Redefined in Bower.
     * @param string $constraint
     * @return string
     */
    public function fixConstraint($constraint)
    {
        return $constraint;
    }

    /**
     * Scans the $package and extracts dependencies to the [[config]].
     * @param PackageInterface $package
     */
    public function scanPackage(PackageInterface $package)
    {
        $extra = $package->getExtra();
        $extra_deps = [];
        foreach ($this->dependencies as $key) {
            $name = $this->name . '-' . $key;
            if (isset($extra[$name])) {
                $extra_deps[$key] = $extra[$name];
            }
        }
        $known_deps = $this->getKnownDeps($package);
        foreach ([$known_deps, $extra_deps] as $deps) {
            if (!empty($deps)) {
                $this->mergeConfig($deps);
            }
        }
    }

    /**
     * Merges the $config over the [[config]].
     * @param array $config
     */
    protected function mergeConfig(array $config)
    {
        foreach ($config as $type => $packages) {
            foreach ($packages as $name => $constraint) {
                $this->addDependency($type, $name, $constraint);
            }
        }
    }

    public function addDependency($type, $name, $constraint)
    {
        if (isset($this->config[$type][$name])) {
            $this->config[$type][$name] = Constraint::merge($this->config[$type][$name], $constraint);
        } else {
            $this->config[$type][$name] = $constraint;
        }
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
     * Returns if the package manager has nonempty dependency list.
     * @return bool
     */
    public function hasDependencies()
    {
        foreach ($this->dependencies as $key) {
            if (isset($this->config[$key]) && $this->config[$key]) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run the given action: show notice, write config and run `perform`.
     * @param string $action the action name
     */
    public function runAction($action)
    {
        $doing = ucfirst(trim($action, 'e')) . 'ing';
        $this->plugin->io->writeError('<info>' . $doing . ' ' . $this->name . ' dependencies...</info>');
        $this->saveConfigs();
        $this->perform($action);
    }

    public function saveConfigs()
    {
        if ($this->rc) {
            $this->writeRc($this->rcfile, $this->rc);
        } else {
            unlink($this->rcfile);
        }
        $this->writeJson($this->file, $this->config);
    }

    abstract public function writeRc($path, $data);

    /**
     * Run installation. Specific for every package manager.
     * @param string $action the action name
     * @void
     */
    protected function perform($action)
    {
        $this->plugin->io->writeError('running ' . $this->getBin());
        if ($this->passthru([$action])) {
            $this->plugin->io->writeError('<error>failed ' . $this->name . ' ' . $action . '</error>');
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
        $pathes = [
            static::buildPath([$this->plugin->getVendorDir(), 'bin', $this->phpBin]),
            static::buildPath([$this->plugin->getComposer()->getConfig()->get('home'), 'vendor', 'bin', $this->phpBin]),
        ];
        foreach ($pathes as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $this->name;
    }

    public static function buildPath($parts)
    {
        return implode(DIRECTORY_SEPARATOR, array_filter($parts));
    }
}
