<?php

namespace App\Tests\Unit\UseCase\Organization;

use App\Entity\Organization;
use App\Entity\OrganizationContact;
use App\Entity\Reservation;
use App\Entity\User;
use App\UseCase\Organization\DeleteOrganization;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteOrganizationTest extends TestCase
{
    public function testRejectsWhenDependenciesExist(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $organization = new Organization();
        $organization->addUser(new User());
        $organization->addContact(new OrganizationContact());
        $organization->addReservation(new Reservation());

        $useCase = new DeleteOrganization($entityManager);

        self::assertFalse($useCase->execute($organization));
    }

    public function testDeletesWhenNoDependencies(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(Organization::class));
        $entityManager->expects(self::once())->method('flush');

        $organization = new Organization();

        $useCase = new DeleteOrganization($entityManager);

        self::assertTrue($useCase->execute($organization));
    }
}
