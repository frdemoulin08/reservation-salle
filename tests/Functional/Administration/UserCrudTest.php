<?php

namespace App\Tests\Functional\Administration;

use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Tests\Functional\DatabaseWebTestCase;

class UserCrudTest extends DatabaseWebTestCase
{
    private function resolveRoleId(): string
    {
        $roleRepository = self::getContainer()->get(RoleRepository::class);
        $preferredRole = $roleRepository->findOneBy(['code' => 'ROLE_SUPER_ADMIN']);
        $preferredId = $preferredRole?->getId();

        if (null === $preferredId) {
            $fallback = $roleRepository->findOneBy([]);
            $preferredId = $fallback?->getId();
        }

        self::assertNotNull($preferredId, 'Aucun rôle disponible pour le test.');

        return (string) $preferredId;
    }

    public function testCreateUserSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/utilisateurs/nouveau');
        self::assertResponseIsSuccessful();

        $csrfToken = (string) $crawler->filter('input[name="user[_token]"]')->attr('value');
        $roleId = $this->resolveRoleId();

        $client->request('POST', '/administration/utilisateurs/nouveau', [
            'user' => [
                'lastname' => 'Durand',
                'firstname' => 'Alice',
                'email' => 'alice.durand@example.test',
                'plainPassword' => 'Abcdef123456@',
                'roleEntities' => [$roleId],
                'isActive' => 1,
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects('/administration/utilisateurs');
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(UserRepository::class);
        self::assertNotNull($repository->findOneBy(['email' => 'alice.durand@example.test']));
    }

    public function testEditUserSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);

        self::assertNotNull($user, 'Utilisateur de test introuvable.');

        $crawler = $client->request('GET', '/administration/utilisateurs/'.$user->getId().'/edition');
        self::assertResponseIsSuccessful();

        $csrfToken = (string) $crawler->filter('input[name="user[_token]"]')->attr('value');
        $roleId = $this->resolveRoleId();

        $client->request('POST', '/administration/utilisateurs/'.$user->getId().'/edition', [
            'user' => [
                'firstname' => 'Frederic',
                'lastname' => 'Demoulin',
                'email' => 'frederic.demoulin@cd08.fr',
                'plainPassword' => '',
                'roleEntities' => [$roleId],
                'isActive' => 1,
                '_token' => $csrfToken,
            ],
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($user->getId());
        self::assertSame('Frederic', $updated?->getFirstname());
    }
}
