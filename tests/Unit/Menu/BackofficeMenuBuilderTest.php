<?php

namespace App\Tests\Unit\Menu;

use App\Menu\BackofficeMenuBuilder;
use App\Service\BackofficeMenuConfigProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class BackofficeMenuBuilderTest extends TestCase
{
    public function testBuildFiltersSectionsAndItemsByRoles(): void
    {
        $config = [
            'sections' => [
                [
                    'key' => 'admin',
                    'label' => 'Administration',
                    'roles' => ['ROLE_SUPER_ADMIN'],
                    'items' => [
                        ['key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'app_admin_index'],
                    ],
                ],
                [
                    'key' => 'public',
                    'label' => 'Public',
                    'items' => [
                        ['key' => 'rooms', 'label' => 'Salles', 'route' => 'app_rooms'],
                        [
                            'key' => 'secret',
                            'label' => 'Secret',
                            'roles' => ['ROLE_SUPER_ADMIN'],
                            'route' => 'app_secret',
                        ],
                    ],
                ],
            ],
        ];

        $configProvider = $this->createMock(BackofficeMenuConfigProvider::class);
        $configProvider->expects(self::once())
            ->method('getConfig')
            ->willReturn($config);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')
            ->willReturnCallback(static fn (string $role): bool => 'ROLE_APP_MANAGER' === $role);

        $builder = new BackofficeMenuBuilder($configProvider, $authorizationChecker);
        $menu = $builder->build();

        self::assertCount(1, $menu);
        self::assertSame('public', $menu[0]['key']);
        self::assertCount(1, $menu[0]['items']);
        self::assertSame('rooms', $menu[0]['items'][0]['key']);
    }

    public function testBuildKeepsOnlyAllowedChildren(): void
    {
        $config = [
            'sections' => [
                [
                    'key' => 'catalog',
                    'label' => 'Catalogue',
                    'items' => [
                        [
                            'key' => 'settings',
                            'label' => 'Paramétrage',
                            'children' => [
                                [
                                    'key' => 'general',
                                    'label' => 'Général',
                                    'roles' => ['ROLE_SUPER_ADMIN'],
                                    'route' => 'app_admin_general',
                                ],
                                [
                                    'key' => 'profile',
                                    'label' => 'Profil',
                                    'roles' => ['ROLE_APP_MANAGER'],
                                    'route' => 'app_admin_profile',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $configProvider = $this->createMock(BackofficeMenuConfigProvider::class);
        $configProvider->expects(self::once())
            ->method('getConfig')
            ->willReturn($config);

        $authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authorizationChecker->method('isGranted')
            ->willReturnCallback(static fn (string $role): bool => 'ROLE_APP_MANAGER' === $role);

        $builder = new BackofficeMenuBuilder($configProvider, $authorizationChecker);
        $menu = $builder->build();

        self::assertCount(1, $menu);
        self::assertCount(1, $menu[0]['items']);
        self::assertCount(1, $menu[0]['items'][0]['children']);
        self::assertSame('profile', $menu[0]['items'][0]['children'][0]['key']);
    }
}
