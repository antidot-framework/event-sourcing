<?php

namespace AntidotTest\EventSource\Infrastructure\Framework;

use Antidot\EventSource\Infrastructure\Framework\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    public function testItShouldReturnThePackageConfig(): void
    {
        $configProvider = new ConfigProvider();

        $config = $configProvider();
        $this->assertIsArray($config);

        $this->assertSame(ConfigProvider::DEPENDENCIES, $config['dependencies']);
    }
}
