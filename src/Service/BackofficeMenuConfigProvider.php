<?php

namespace App\Service;

use App\Repository\BackofficeMenuConfigRepository;

class BackofficeMenuConfigProvider
{
    /**
     * @param array<string, mixed> $defaultConfig
     */
    public function __construct(
        private readonly BackofficeMenuConfigRepository $repository,
        private readonly array $defaultConfig,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $activeConfig = $this->repository->findActive();
        if ($activeConfig) {
            return $activeConfig->getConfig();
        }

        return $this->defaultConfig;
    }
}
