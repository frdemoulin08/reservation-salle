<?php

namespace App\Tests\Unit\UseCase\EquipmentType;

use App\Entity\EquipmentType;
use App\UseCase\EquipmentType\UpdateEquipmentType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateEquipmentTypeTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateEquipmentType($entityManager);

        $useCase->execute(new EquipmentType());
    }
}
