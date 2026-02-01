<?php

namespace App\Menu;

use App\Service\BackofficeMenuConfigProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BackofficeMenuBuilder
{
    public function __construct(
        private readonly BackofficeMenuConfigProvider $configProvider,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function build(): array
    {
        $config = $this->configProvider->getConfig();
        $sections = $config['sections'] ?? [];
        $menu = [];

        foreach ($sections as $section) {
            if (!$this->isAllowed($section)) {
                continue;
            }

            $items = [];
            foreach ($section['items'] ?? [] as $item) {
                if (!$this->isAllowed($item)) {
                    continue;
                }

                $items[] = $this->normalizeItem($item);
            }

            if (!$items) {
                continue;
            }

            $menu[] = [
                'key' => $section['key'] ?? null,
                'label' => $section['label'] ?? null,
                'items' => $items,
            ];
        }

        return $menu;
    }

    /**
     * @param array<string, mixed> $node
     */
    private function isAllowed(array $node): bool
    {
        $roles = $node['roles'] ?? null;
        if (!$roles) {
            return true;
        }

        foreach ($roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    private function normalizeItem(array $item): array
    {
        $children = [];
        foreach ($item['children'] ?? [] as $child) {
            if (!$this->isAllowed($child)) {
                continue;
            }

            $children[] = $this->normalizeItem($child);
        }

        return [
            'key' => $item['key'] ?? null,
            'label' => $item['label'] ?? null,
            'route' => $item['route'] ?? null,
            'route_params' => $item['route_params'] ?? [],
            'url' => $item['url'] ?? null,
            'children' => $children,
        ];
    }
}
