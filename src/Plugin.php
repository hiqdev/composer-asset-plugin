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
use Composer\Package\CompletePackage;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    const LOCK_FILE = 'composer-asset-plugin.lock';

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var IOInterface
     */
    protected $io;

    protected $managers = ['bower', 'npm'];

    protected $packages;

    /**
     * Initializes the plugin object with passed $composer and $io.
     * Also initializes package managers.
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        foreach ($this->managers as $m) {
            $class = 'hiqdev\composerassetplugin\\' . ucfirst($m);
            $managers[$m] = new $class($this);
        }
        $this->managers = $managers;
    }

    /**
     * Returns list of events the plugin wants to listen.
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
     * Perform install.
     *
     * @param Event $event
     */
    public function onPostInstall(Event $event)
    {
        $lockFile = new JsonFile(LOCK_FILE);
        if ($lockFile->exists()) {
            $this->loadPackages($lockFile);
            $this->installPackages();
        } else {
            $this->onPostUpdate($event);
        }
    }

    /**
     * Perform update.
     *
     * @param Event $event
     */
    public function onPostUpdate(Event $event)
    {
        $this->scanPackages();
        $this->installPackages();
    }

    public function setPackages(array $packages)
    {
        $this->packages = $packages;
    }

    public function getPackages()
    {
        if ($this->packages === null) {
            $this->packages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        }

        return $this->packages;
    }

    /**
     * Scan packages from the composer object.
     */
    protected function scanPackages()
    {
        foreach ($this->getPackages() as $package) {
            if ($package instanceof \Composer\Package\CompletePackage) {
                foreach ($this->managers as $m) {
                    $m->scanPackage($package);
                }
            }
        }
    }

    /**
     * Load packages from given lock file.
     */
    protected function loadPackages(JsonFile $lockFile)
    {
        $lock = $lockFile->read();
        foreach ($this->managers as $name => $m) {
            $m->loadPackages($lock[$name]);
        }
    }

    /**
     * Install packages after loading/scanning.
     */
    protected function installPackages()
    {
        foreach ($this->managers as $m) {
            $m->installPackages();
        }
    }
}
