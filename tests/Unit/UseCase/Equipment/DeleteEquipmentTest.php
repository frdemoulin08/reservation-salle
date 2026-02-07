<?php

namespace App\Tests\Unit\UseCase\Equipment;

use App\Entity\Equipment;
use App\UseCase\Equipment\DeleteEquipment;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteEquipmentTest extends TestCase
{
    public function testRemovesAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(Equipment::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new DeleteEquipment($entityManager);

        $useCase->execute(new Equipment());
    }
}
