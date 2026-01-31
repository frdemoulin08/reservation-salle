<?php

namespace App\Tests\Unit\Service;

use App\Service\GitHubVersionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GitHubVersionServiceTest extends TestCase
{
    public function testReturnsLatestTag(): void
    {
        $client = new MockHttpClient([
            new MockResponse(json_encode([
                ['name' => 'v2.0.0'],
                ['name' => 'v1.9.0'],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $cache = new ArrayAdapter();

        $service = new GitHubVersionService($client, $cache, 'acme/repo');

        self::assertSame('v2.0.0', $service->getLatestTag());
    }

    public function testUsesCacheToAvoidMultipleRequests(): void
    {
        $calls = 0;
        $client = new MockHttpClient(function () use (&$calls) {
            ++$calls;

            return new MockResponse(json_encode([
                ['name' => 'v1.0.0'],
            ], JSON_THROW_ON_ERROR));
        });
        $cache = new ArrayAdapter();

        $service = new GitHubVersionService($client, $cache, 'acme/repo');

        $service->getLatestTag();
        $service->getLatestTag();

        self::assertSame(1, $calls);
    }

    public function testReturnsNullOnFailure(): void
    {
        $client = new MockHttpClient(function () {
            throw new \RuntimeException('boom');
        });
        $cache = new ArrayAdapter();

        $service = new GitHubVersionService($client, $cache, 'acme/repo');

        self::assertNull($service->getLatestTag());
    }
}
