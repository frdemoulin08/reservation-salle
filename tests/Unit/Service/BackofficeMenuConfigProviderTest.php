<?php

namespace App\Tests\Unit\Service;

use App\Entity\BackofficeMenuConfig;
use App\Repository\BackofficeMenuConfigRepository;
use App\Service\BackofficeMenuConfigProvider;
use PHPUnit\Framework\TestCase;

class BackofficeMenuConfigProviderTest extends TestCase
{
    public function testReturnsDefaultConfigWhenNoActiveConfig(): void
    {
        $repository = $this->createMock(BackofficeMenuConfigRepository::class);
        $repository->expects(self::once())
            ->method('findActive')
            ->willReturn(null);

        $defaultConfig = ['sections' => [['key' => 'default']]];

        $provider = new BackofficeMenuConfigProvider($repository, $defaultConfig);

        self::assertSame($defaultConfig, $provider->getConfig());
    }

    public function testReturnsActiveConfigWhenAvailable(): void
    {
        $config = new BackofficeMenuConfig();
        $config->setConfig(['sections' => [['key' => 'override']]]);

        $repository = $this->createMock(BackofficeMenuConfigRepository::class);
        $repository->expects(self::once())
            ->method('findActive')
            ->willReturn($config);

        $provider = new BackofficeMenuConfigProvider($repository, ['sections' => []]);

        self::assertSame(['sections' => [['key' => 'override']]], $provider->getConfig());
    }
}
