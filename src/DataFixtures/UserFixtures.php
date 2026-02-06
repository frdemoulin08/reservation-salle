<?php

namespace App\DataFixtures;

use App\Entity\Organization;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const ADMIN_USER = 'admin-user';
    public const ADMINISTRATIVE_MANAGER_USER = 'administrative-manager-user';
    public const APP_MANAGER_USER = 'app-manager-user';
    public const APP_MANAGER_BUSINESS_USER = 'app-manager-business-user';
    public const SUPERVISOR_USER = 'supervisor-user';
    public const CUSTOMER_USER = 'customer-user';
    public const EXTERNAL_USER = 'external-user';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $firstNames = ['Claire', 'Marc', 'Julie', 'Hugo', 'Emma', 'Nicolas', 'Camille', 'Lucas', 'Chloé', 'Pierre', 'Sarah', 'Thomas', 'Léa', 'Antoine', 'Manon', 'Adrien'];
        $lastNames = ['Dupont', 'Leroy', 'Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Moreau', 'Simon', 'Laurent', 'Garcia', 'Roux', 'Fournier'];

        $superAdmin = $this->createUser(
            $manager,
            'frederic.demoulin@cd08.fr',
            'Frederic',
            'Demoulin',
            'Abcdef123456@',
            [$this->getReference(RoleFixtures::ROLE_SUPER_ADMIN, Role::class)]
        );
        $this->addReference(self::ADMIN_USER, $superAdmin);

        $administrativeManager = $this->createUser(
            $manager,
            'metier.admin@cd08.fr',
            'Marion',
            'Leblanc',
            'Abcdef123456@',
            [$this->getReference(RoleFixtures::ROLE_APP_MANAGER, Role::class)]
        );
        $this->addReference(self::ADMINISTRATIVE_MANAGER_USER, $administrativeManager);

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

        $organization = $this->getReference(OrganizationFixtures::ORG_MAIN, Organization::class);

        $customer = $this->createUser(
            $manager,
            'claire.dupont@asso-exemple.org',
            'Claire',
            'Dupont',
            'Abcdef123456@',
            [],
            $organization
        );
        $this->addReference(self::CUSTOMER_USER, $customer);

        $external = $this->createUser(
            $manager,
            'marc.leroy@asso-exemple.org',
            'Marc',
            'Leroy',
            'Abcdef123456@',
            [],
            $organization
        );
        $this->addReference(self::EXTERNAL_USER, $external);

        $organizations = $manager->getRepository(Organization::class)->findAll();
        $counter = 1;

        foreach ($organizations as $org) {
            for ($i = 0; $i < 2; ++$i) {
                $firstName = $firstNames[$counter % count($firstNames)];
                $lastName = $lastNames[$counter % count($lastNames)];
                $domain = $this->slugify($org->getDisplayName() ?: $org->getLegalName());
                if ('' === $domain) {
                    $domain = sprintf('org%d', $counter);
                }

                $email = sprintf(
                    '%s.%s.%02d@%s.example.org',
                    $this->slugify($firstName),
                    $this->slugify($lastName),
                    $counter,
                    $domain
                );

                $this->createUser(
                    $manager,
                    $email,
                    $firstName,
                    $lastName,
                    'Abcdef123456@',
                    [],
                    $org
                );

                ++$counter;
            }
        }

        $this->createUser(
            $manager,
            sprintf('usager.%02d@exemple.org', $counter++),
            $firstNames[$counter % count($firstNames)],
            $lastNames[$counter % count($lastNames)],
            'Abcdef123456@',
            [],
            null
        );
        $this->createUser(
            $manager,
            sprintf('usager.%02d@exemple.org', $counter++),
            $firstNames[$counter % count($firstNames)],
            $lastNames[$counter % count($lastNames)],
            'Abcdef123456@',
            [],
            null
        );

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [RoleFixtures::class, OrganizationFixtures::class];
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
        ?Organization $organization = null,
    ): User {
        $user = new User();
        $user->setEmail($email);
        $user->setFirstname($firstname);
        $user->setLastname($lastname);
        $user->setIsActive(true);
        $user->setOrganization($organization);

        foreach ($roles as $role) {
            $user->addRoleEntity($role);
        }

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        return $user;
    }

    private function slugify(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $value);
        if (false !== $normalized) {
            $value = $normalized;
        }

        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');

        return $value;
    }
}
