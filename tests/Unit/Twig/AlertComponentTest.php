<?php

namespace App\Tests\Unit\Twig;

use App\Twig\Components\Alert;
use PHPUnit\Framework\TestCase;

class AlertComponentTest extends TestCase
{
    public function testWrapperClassUsesWarningStyles(): void
    {
        $component = new Alert();
        $component->type = 'warning';

        self::assertStringContainsString('text-fg-warning', $component->getWrapperClass());
        self::assertStringContainsString('bg-warning-soft', $component->getWrapperClass());
    }

    public function testWrapperClassFallsBackToInfoStyles(): void
    {
        $component = new Alert();
        $component->type = 'unknown';

        self::assertStringContainsString('text-fg-brand-strong', $component->getWrapperClass());
        self::assertStringContainsString('bg-brand-softer', $component->getWrapperClass());
    }
}
