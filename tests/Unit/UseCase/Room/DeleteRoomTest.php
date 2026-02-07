<?php

namespace App\Tests\Unit\UseCase\Room;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\RoomDocument;
use App\Entity\RoomEquipment;
use App\Entity\RoomPricing;
use App\Entity\RoomService;
use App\UseCase\Room\DeleteRoom;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteRoomTest extends TestCase
{
    public function testRejectsWhenDependenciesExist(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $room = new Room();
        $room->addRoomEquipment(new RoomEquipment());
        $room->addRoomService(new RoomService());
        $room->addRoomDocument(new RoomDocument());
        $room->addRoomPricing(new RoomPricing());
        $room->addReservation(new Reservation());

        $useCase = new DeleteRoom($entityManager);

        self::assertFalse($useCase->execute($room));
    }

    public function testDeletesWhenNoDependencies(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(Room::class));
        $entityManager->expects(self::once())->method('flush');

        $room = new Room();

        $useCase = new DeleteRoom($entityManager);

        self::assertTrue($useCase->execute($room));
    }
}
