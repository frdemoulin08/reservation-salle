<?php

namespace App\Twig;

use App\Menu\BackofficeMenuBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BackofficeMenuExtension extends AbstractExtension
{
    public function __construct(
        private readonly BackofficeMenuBuilder $menuBuilder,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('backoffice_menu', [$this, 'getBackofficeMenu']),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getBackofficeMenu(): array
    {
        return $this->menuBuilder->build();
    }
}
