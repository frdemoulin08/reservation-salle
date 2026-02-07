<?php

namespace App\Tests\Unit\UseCase\EquipmentType;

use App\Entity\EquipmentType;
use App\UseCase\EquipmentType\CreateEquipmentType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateEquipmentTypeTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(EquipmentType::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateEquipmentType($entityManager);

        $useCase->execute(new EquipmentType());
    }
}
