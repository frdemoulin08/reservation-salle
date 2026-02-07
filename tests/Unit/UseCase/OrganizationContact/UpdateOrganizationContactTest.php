<?php

namespace App\Tests\Unit\UseCase\OrganizationContact;

use App\Entity\OrganizationContact;
use App\UseCase\OrganizationContact\UpdateOrganizationContact;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateOrganizationContactTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateOrganizationContact($entityManager);

        $useCase->execute(new OrganizationContact());
    }
}
