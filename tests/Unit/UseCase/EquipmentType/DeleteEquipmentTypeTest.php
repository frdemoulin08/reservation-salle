<?php

namespace App\Tests\Unit\UseCase\EquipmentType;

use App\Entity\Equipment;
use App\Entity\EquipmentType;
use App\Entity\RoomEquipment;
use App\Entity\VenueEquipment;
use App\UseCase\EquipmentType\DeleteEquipmentType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteEquipmentTypeTest extends TestCase
{
    public function testRejectsWhenUsed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $equipmentType = new EquipmentType();
        $equipmentType->getRoomEquipments()->add(new RoomEquipment());
        $equipmentType->getVenueEquipments()->add(new VenueEquipment());
        $equipmentType->getEquipments()->add(new Equipment());

        $useCase = new DeleteEquipmentType($entityManager);

        self::assertFalse($useCase->execute($equipmentType));
    }

    public function testDeletesWhenUnused(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(EquipmentType::class));
        $entityManager->expects(self::once())->method('flush');

        $equipmentType = new EquipmentType();

        $useCase = new DeleteEquipmentType($entityManager);

        self::assertTrue($useCase->execute($equipmentType));
    }
}
