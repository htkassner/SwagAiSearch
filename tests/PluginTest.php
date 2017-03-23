<?php

namespace SwagAiSearch\Tests;

use SwagAiSearch\SwagAiSearch as Plugin;
use Shopware\Components\Test\Plugin\TestCase;

class PluginTest extends TestCase
{
    protected static $ensureLoadedPlugins = [
        'SwagAiSearch' => []
    ];

    public function testCanCreateInstance()
    {
        /** @var Plugin $plugin */
        $plugin = Shopware()->Container()->get('kernel')->getPlugins()['SwagAiSearch'];

        $this->assertInstanceOf(Plugin::class, $plugin);
    }
}
