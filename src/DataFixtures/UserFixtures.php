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

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('frederic.demoulin@cd08.fr');
        $user->setFirstname('Frederic');
        $user->setLastname('Demoulin');
        $user->setIsActive(true);
        $role = $this->getReference(RoleFixtures::ROLE_SUPER_ADMIN, Role::class);
        $user->addRoleEntity($role);

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'Abcdef123456@');
        $user->setPassword($hashedPassword);

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::ADMIN_USER, $user);
    }

    public function getDependencies(): array
    {
        return [RoleFixtures::class];
    }
}
