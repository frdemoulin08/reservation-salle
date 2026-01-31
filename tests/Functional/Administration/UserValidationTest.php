<?php

namespace App\Tests\Functional\Administration;

use App\Repository\RoleRepository;
use App\Tests\Functional\DatabaseWebTestCase;

class UserValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="user[_token]"]')->attr('value');
    }

    private function getRoleId(): string
    {
        $repository = self::getContainer()->get(RoleRepository::class);
        $role = $repository->findOneBy(['code' => 'ROLE_SUPER_ADMIN']) ?? $repository->findOneBy([]);

        self::assertNotNull($role, 'Aucun rôle disponible pour le test.');

        return (string) $role->getId();
    }

    public function testCreateUserRequiresRole(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/utilisateurs/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/utilisateurs/nouveau', [
            'user' => [
                'lastname' => 'SansRole',
                'firstname' => 'Test',
                'email' => 'sansrole@example.test',
                'plainPassword' => 'Abcdef123456@',
                'roleEntities' => [],
                'isActive' => 1,
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#user_roleEntities-error', 'Au moins un rôle est obligatoire');
    }

    public function testCreateUserRejectsInvalidEmail(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/utilisateurs/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/utilisateurs/nouveau', [
            'user' => [
                'lastname' => 'Email',
                'firstname' => 'Invalide',
                'email' => 'pas-un-email',
                'plainPassword' => 'Abcdef123456@',
                'roleEntities' => [$this->getRoleId()],
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#user_email-error', 'Le format de l’email est invalide');
    }
}
