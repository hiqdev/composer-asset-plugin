<?php

/*
 * Composer plugin for bower/npm assets
 *
 * @link      https://github.com/hiqdev/composer-asset-plugin
 * @package   composer-asset-plugin
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2015, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\composerassetplugin\tests\unit;

use Composer\Composer;
use Composer\Config;
use hiqdev\composerassetplugin\Plugin;

/**
 * Class PluginTest
 * @package hiqdev\composerassetplugin\tests\unit
 */
class PluginTest extends \PHPUnit_Framework_TestCase
{
    private $object;
    private $io;
    private $composer;
    private $event;
    private $packages = [];

    public function setUp()
    {
        parent::setUp();
        $this->composer = new Composer();
        $this->composer->setConfig(new Config());
        $this->io = $this->getMock('Composer\IO\IOInterface');
        $this->event = $this->getMock('Composer\Script\Event', [], ['test', $this->composer, $this->io]);

        $this->object = new Plugin();
        $this->object->setPackages($this->packages);
        $this->object->activate($this->composer, $this->io);
    }

    public function testGetPackages()
    {
        $this->assertSame($this->packages, $this->object->getPackages());
    }

    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', $this->object->getSubscribedEvents());
    }

    public function testOnPostInstall()
    {
        $this->object->onPostInstall($this->event);
    }
}
