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
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    protected $composer;
    protected $io;

    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Returns list of events the plugin wants to listen.
     *
     * @return array list of events
     */
    public static function getSubscribedEvents()
    {
        return [
            'post-install-cmd' => [
                ['onPostInstall', 0],
            ],
            'post-update-cmd' => [
                ['onPostUpdate', 0],
            ],
        ];
    }

    public function onPostInstall(Event $event)
    {
    }

    public function onPostUpdate(Event $event)
    {
    }
}
