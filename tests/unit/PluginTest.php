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

use hiqdev\composerassetplugin\Plugin;

class PluginTest extends \PHPUnit_Framework_TestCase
{
    private $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Plugin();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertInternalType('array', $this->object->getSubscribedEvents());
    }
}
