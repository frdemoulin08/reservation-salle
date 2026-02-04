<?php

namespace App\Service;

class BackofficeMenuConfigProvider
{
    /**
     * @param array<string, mixed> $defaultConfig
     */
    public function __construct(
        private readonly array $defaultConfig,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->defaultConfig;
    }
}
