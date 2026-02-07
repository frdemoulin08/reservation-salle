<?php

namespace App\Tests\Unit\UseCase\Equipment;

use App\Entity\Equipment;
use App\UseCase\Equipment\CreateEquipment;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateEquipmentTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(Equipment::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateEquipment($entityManager);

        $useCase->execute(new Equipment());
    }
}
