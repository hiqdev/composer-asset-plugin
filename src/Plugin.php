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

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\InstallerEvent;
use Composer\Installer\InstallerEvents;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\PackageInterface;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
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
     * @var Pool
     */
    protected $pool;

    /**
     * List of the available package managers/
     * Initialized at activate.
     * @var array|PackageManager[]
     * @see activate
     */
    protected $managers = [
        'bower' => 'hiqdev\composerassetplugin\Bower',
        'npm'   => 'hiqdev\composerassetplugin\Npm',
    ];

    /**
     * @var PackageInterface[] the array of active composer packages
     */
    protected $packages;

    /**
     * @var string absolute path to vendor directory.
     */
    protected $vendorDir;

    /**
     *
     */
    protected $requires = [];

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

        #$rm = $composer->getRepositoryManager();

        #$rm->setRepositoryClass('assets', 'hiqdev\composerassetplugin\AssetRepository');
        #$rm->addRepository($rm->createRepository('assets', ['plugin' => $this]));
    }

    public function getComposer()
    {
        return $this->composer;
    }

    public function hasManager($name)
    {
        return isset($this->managers[$name]);
    }

    public function getManager($name)
    {
        return $this->managers[$name];
    }

    /**
     * Returns list of events the plugin is subscribed to.
     *
     * @return array list of events
     */
    public static function getSubscribedEvents()
    {
        return [
#           InstallerEvents::PRE_DEPENDENCIES_SOLVING => array(
#               array('onPreDependenciesSolving', 0),
#           ),
#           PluginEvents::COMMAND => array(
#               ['onCommand', 0],
#           ),
            ScriptEvents::POST_INSTALL_CMD => [
                ['onPostInstall', 0],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['onPostUpdate', 0],
            ],
        ];
    }

    /**
     * @param InstallerEvent $event
     */
    public function onPreDependenciesSolving(InstallerEvent $event)
    {
        $pool = $event->getPool();
        for ($i=1; $i<= $pool->count(); $i++) {
            $package = $pool->packageById($i);
            $this->scanAssetDependencies($package);
        }
    }

    public function scanAssetDependencies(PackageInterface $package)
    {
        static $deptypes = [
            'dependencies'      => 'getRequires',
            'devDependencies'   => 'getDevRequires',
        ];
        $res = [];
        foreach ($deptypes as $deptype => $method) {
            $requires = $package->$method();
            foreach ($requires as $reqkey => $require) {
                $target = $require->getTarget();
                if (strpos($target, '/') === false) {
                    continue;
                }
                list($vendor, $name) = explode('/', $target);
                if (substr($vendor, -6) !== '-asset') {
                    continue;
                }
                list($manager, $asset) = explode('-', $vendor);
                #var_dump($target . ' ' . $require->getPrettyConstraint());
                if ($this->hasManager($manager)) {
                    $this->getManager($manager)->setKnownDeps($package, $deptype, $name, $require->getPrettyConstraint());
                    /*
                    unset($requires[$reqkey]);
                    $method[0] = 's';
                    if (method_exists($package, $method)) {
                        $package->{$method}($requires);
                    }
                    */
                }
            }
        }
    }

    /**
     * @param CommandEvent $event
     */
    public function onCommand(CommandEvent $event)
    {
        return;
        $repositories = $this->composer->getRepositoryManager()->getRepositories();
        foreach ($repositories as $repository) {
            foreach ($repository->getPackages() as $package) {
            }
        }
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
            $this->packages[] = $this->composer->getPackage();
        }

        return $this->packages;
    }

    /**
     * Returns package with given name if exists.
     * @param string $name package name
     * @return \Composer\Package\PackageInterface|null
     */
    public function findPackage($name)
    {
        foreach ($this->getPackages() as $package) {
            if ($name === $package->getName()) {
                return $package;
            }
        }
    }

    /**
     * Scan packages from the composer objects.
     * @void
     */
    protected function scanPackages()
    {
        $rootPackage = $this->composer->getPackage();
        if ($rootPackage) {
            $extra = $rootPackage->getExtra();
            foreach ($this->managers as $manager) {
                $var = $manager->getName() . '-asset-library';
                if (isset($extra['asset-installer-paths'][$var])) {
                    $manager->setDestination($extra['asset-installer-paths'][$var]);
                }
            }
        }
        foreach ($this->getPackages() as $package) {
            if ($package instanceof \Composer\Package\CompletePackageInterface) {
                $this->scanAssetDependencies($package);
            }
        }
        foreach ($this->getPackages() as $package) {
            if ($package instanceof \Composer\Package\CompletePackageInterface) {
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
            if ($m->hasDependencies()) {
                $m->runAction($action);
            }
        }
        chdir($dir);
    }

    /**
     * Get absolute path to composer vendor dir.
     * @return string
     */
    public function getVendorDir()
    {
        if ($this->vendorDir === null) {
            $this->vendorDir = $this->composer->getConfig()->get('vendor-dir', '/');
        }

        return $this->vendorDir;
    }
}
