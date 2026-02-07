<?php

namespace App\Tests\Unit\UseCase\OrganizationContact;

use App\Entity\OrganizationContact;
use App\Entity\Reservation;
use App\UseCase\OrganizationContact\DeleteOrganizationContact;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class DeleteOrganizationContactTest extends TestCase
{
    public function testRejectsWhenUsed(): void
    {
        $contact = new OrganizationContact();

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('count')
            ->with(['organizationContact' => $contact])
            ->willReturn(2);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('getRepository')
            ->with(Reservation::class)
            ->willReturn($repository);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $useCase = new DeleteOrganizationContact($entityManager);

        self::assertFalse($useCase->execute($contact));
    }

    public function testDeletesWhenUnused(): void
    {
        $contact = new OrganizationContact();

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects(self::once())
            ->method('count')
            ->with(['organizationContact' => $contact])
            ->willReturn(0);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('getRepository')
            ->with(Reservation::class)
            ->willReturn($repository);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(OrganizationContact::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new DeleteOrganizationContact($entityManager);

        self::assertTrue($useCase->execute($contact));
    }
}
