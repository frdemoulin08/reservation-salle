<?php

namespace App\Tests\Unit\Twig;

use App\Service\GitHubVersionService;
use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;

class AppExtensionTest extends TestCase
{
    public function testGlobalsExposeAppVersion(): void
    {
        $service = $this->createMock(GitHubVersionService::class);
        $service
            ->expects(self::once())
            ->method('getLatestTag')
            ->willReturn('v1.2.3');

        $extension = new AppExtension($service);
        $globals = $extension->getGlobals();

        self::assertArrayHasKey('app_version', $globals);
        self::assertSame('v1.2.3', $globals['app_version']);
    }
}
