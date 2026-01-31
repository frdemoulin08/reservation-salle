<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public const ROLE_SUPER_ADMIN = 'role_super_admin';
    public const ROLE_BUSINESS_ADMIN = 'role_business_admin';
    public const ROLE_APP_MANAGER = 'role_app_manager';
    public const ROLE_SUPERVISOR = 'role_supervisor';

    public function load(ObjectManager $manager): void
    {
        $this->createRole(
            $manager,
            'ROLE_SUPER_ADMIN',
            'Super Administrator',
            'Gouvernance globale de l’application (technique et fonctionnelle).',
            self::ROLE_SUPER_ADMIN
        );

        $this->createRole(
            $manager,
            'ROLE_BUSINESS_ADMIN',
            'Business Administrator',
            'Paramétrage fonctionnel de l’application.',
            self::ROLE_BUSINESS_ADMIN
        );

        $this->createRole(
            $manager,
            'ROLE_APP_MANAGER',
            'Application Manager',
            'Gestion opérationnelle (réservations, relation usager).',
            self::ROLE_APP_MANAGER
        );

        $this->createRole(
            $manager,
            'ROLE_SUPERVISOR',
            'Supervisor',
            'Accès au pilotage, tableaux de bord et indicateurs.',
            self::ROLE_SUPERVISOR
        );

        $manager->flush();
    }

    private function createRole(
        ObjectManager $manager,
        string $code,
        string $label,
        string $description,
        string $reference,
    ): void {
        $role = new Role();
        $role->setCode($code);
        $role->setLabel($label);
        $role->setDescription($description);
        $role->setIsActive(true);

        $manager->persist($role);
        $this->addReference($reference, $role);
    }
}
