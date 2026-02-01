<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const ADMIN_USER = 'admin-user';
    public const BUSINESS_ADMIN_USER = 'business-admin-user';
    public const APP_MANAGER_USER = 'app-manager-user';
    public const APP_MANAGER_BUSINESS_USER = 'app-manager-business-user';
    public const SUPERVISOR_USER = 'supervisor-user';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $superAdmin = $this->createUser(
            $manager,
            'frederic.demoulin@cd08.fr',
            'Frederic',
            'Demoulin',
            'Abcdef123456@',
            [$this->getReference(RoleFixtures::ROLE_SUPER_ADMIN, Role::class)]
        );
        $this->addReference(self::ADMIN_USER, $superAdmin);

        $businessAdmin = $this->createUser(
            $manager,
            'metier.admin@cd08.fr',
            'Marion',
            'Leblanc',
            'Abcdef123456@',
            [$this->getReference(RoleFixtures::ROLE_BUSINESS_ADMIN, Role::class)]
        );
        $this->addReference(self::BUSINESS_ADMIN_USER, $businessAdmin);

        $appManager = $this->createUser(
            $manager,
            'gestion.admin@cd08.fr',
            'Luc',
            'Garnier',
            'Abcdef123456@',
            [$this->getReference(RoleFixtures::ROLE_APP_MANAGER, Role::class)]
        );
        $this->addReference(self::APP_MANAGER_USER, $appManager);

        $appManagerBusiness = $this->createUser(
            $manager,
            'gestion.metier@cd08.fr',
            'Sophie',
            'Morel',
            'Abcdef123456@',
            [
                $this->getReference(RoleFixtures::ROLE_APP_MANAGER, Role::class),
                $this->getReference(RoleFixtures::ROLE_BUSINESS_ADMIN, Role::class),
            ]
        );
        $this->addReference(self::APP_MANAGER_BUSINESS_USER, $appManagerBusiness);

        $supervisor = $this->createUser(
            $manager,
            'clement.jacquet@cd08.fr',
            'Clément',
            'JACQUET',
            'Abcdef123456@',
            [$this->getReference(RoleFixtures::ROLE_SUPERVISOR, Role::class)]
        );
        $this->addReference(self::SUPERVISOR_USER, $supervisor);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RoleFixtures::class];
    }

    /**
     * @param Role[] $roles
     */
    private function createUser(
        ObjectManager $manager,
        string $email,
        string $firstname,
        string $lastname,
        string $plainPassword,
        array $roles,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setIsActive(true);

        foreach ($roles as $role) {
            $user->addRoleEntity($role);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        return $user;
    }
}
