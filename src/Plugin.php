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

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

/**
 * Plugin class.
 *
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * @var string the filename of a lock file. Defaults to `composer-asset-plugin.lock`
     */
    public $lockFile = 'composer-asset-plugin.lock';

    /**
     * @var Composer instance
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    public $io;

    /**
     * List of the available package managers/
     * Initialized at activate.
     * @var array|PackageManager[]
     * @see activate
     */
    protected $managers = [
        'bower' => 'hiqdev\composerassetplugin\Bower',
        'npm' => 'hiqdev\composerassetplugin\Npm',
    ];

    /**
     * @var PackageInterface[] the array of active composer packages
     */
    protected $packages;

    /**
     * Initializes the plugin object with the passed $composer and $io.
     * Also initializes package managers.
     *
     * @param Composer $composer
     * @param IOInterface $io
     * @void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $managers = [];
        $this->composer = $composer;
        $this->io = $io;
        foreach ($this->managers as $name => $class) {
            $managers[$name] = new $class($this);
        }
        $this->managers = $managers;
    }

    /**
     * Returns list of events the plugin is subscribed to.
     *
     * @return array list of events
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                ['onPostInstall', 0],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['onPostUpdate', 0],
            ],
        ];
    }

    /**
     * Perform install. Called by composer after install.
     *
     * @param Event $event
     * @void
     */
    public function onPostInstall(Event $event)
    {
        $lockFile = new JsonFile($this->lockFile);
        if ($lockFile->exists()) {
            $this->loadPackages($lockFile);
        } else {
            $this->scanPackages();
        }
        $this->runAction('install');
    }

    /**
     * Perform update. Called by composer after update.
     *
     * @param Event $event
     */
    public function onPostUpdate(Event $event)
    {
        $this->scanPackages();
        $this->runAction('update');
    }

    /**
     * Sets [[packages]].
     *
     * @param PackageInterface[] $packages
     */
    public function setPackages(array $packages)
    {
        $this->packages = $packages;
    }

    /**
     * Gets [[packages]].
     * @return \Composer\Package\PackageInterface[]
     */
    public function getPackages()
    {
        if ($this->packages === null) {
            $this->packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        }

        return $this->packages;
    }

    /**
     * Scan packages from the composer objects.
     * @void
     */
    protected function scanPackages()
    {
        foreach ($this->getPackages() as $package) {
            if ($package instanceof \Composer\Package\CompletePackage) {
                foreach ($this->managers as $manager) {
                    $manager->scanPackage($package);
                }
            }
        }
    }

    /**
     * Load packages from given lock file.
     *
     * @param JsonFile $lockFile
     * @void
     */
    protected function loadPackages(JsonFile $lockFile)
    {
        $lock = $lockFile->read();
        foreach ($this->managers as $name => $m) {
            $m->setConfig($lock[$name]);
        }
    }

    /**
     * Install packages after loading/scanning.
     * @param string $action
     * @void
     */
    protected function runAction($action)
    {
        $dir = getcwd();
        chdir($this->getVendorDir());
        foreach ($this->managers as $m) {
            $m->runAction($action);
        }
        chdir($dir);
    }

    /**
     * Get absolute path to composer vendor dir.
     * @return string
     */
    public function getVendorDir()
    {
        return dirname(__DIR__) . $this->composer->getConfig()->get('vendor-dir');
    }
}
