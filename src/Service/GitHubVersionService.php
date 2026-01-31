<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubVersionService
{
    public function __construct(
        private HttpClientInterface $client,
        private CacheInterface $cache,
        private string $githubRepo,
    ) {
    }

    public function getLatestTag(): ?string
    {
        return $this->cache->get('github_latest_tag', function (ItemInterface $item) {
            $item->expiresAfter(3600);

            try {
                $response = $this->client->request(
                    'GET',
                    "https://api.github.com/repos/{$this->githubRepo}/tags",
                    [
                        'headers' => [
                            'Accept' => 'application/vnd.github+json',
                        ],
                        'timeout' => 5,
                    ]
                );

                $tags = $response->toArray();

                return $tags[0]['name'] ?? null;
            } catch (\Throwable) {
                return null;
            }
        });
    }
}
