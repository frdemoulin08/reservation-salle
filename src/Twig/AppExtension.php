<?php

namespace App\Twig;

use App\Service\GitHubVersionService;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private GitHubVersionService $gitHubVersionService,
    ) {
    }

    public function getGlobals(): array
    {
        return [
            'app_version' => $this->gitHubVersionService->getLatestTag(),
        ];
    }
}
