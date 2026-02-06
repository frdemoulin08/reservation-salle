<?php

namespace App\Tests\Unit\Service;

use App\Service\CompanyLookupService;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CompanyLookupServiceTest extends TestCase
{
    public function testNormalizeSiretStripsNonDigits(): void
    {
        $service = $this->createService();

        self::assertSame('12345678901234', $service->normalizeSiret('12 34 56 78 90 12 34'));
        self::assertSame('12345678901234', $service->normalizeSiret('12.34.56.78.90.12.34'));
    }

    public function testValidSiretPassesLuhn(): void
    {
        $service = $this->createService();

        self::assertTrue($service->isValidSiret('10000000000016'));
        self::assertFalse($service->isValidSiret('10000000000015'));
        self::assertFalse($service->isValidSiret('123'));
    }

    private function createService(): CompanyLookupService
    {
        return new CompanyLookupService($this->createStub(HttpClientInterface::class), 'https://example.test');
    }
}
