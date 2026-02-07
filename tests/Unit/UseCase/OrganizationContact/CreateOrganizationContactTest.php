<?php

namespace App\Tests\Unit\UseCase\OrganizationContact;

use App\Entity\OrganizationContact;
use App\UseCase\OrganizationContact\CreateOrganizationContact;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateOrganizationContactTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(OrganizationContact::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateOrganizationContact($entityManager);

        $useCase->execute(new OrganizationContact());
    }
}
