<?php

namespace App\Tests\Unit\UseCase\RoomType;

use App\Entity\Room;
use App\Entity\RoomType;
use App\UseCase\RoomType\DeleteRoomType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteRoomTypeTest extends TestCase
{
    public function testRejectsWhenUsed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $roomType = new RoomType();
        $roomType->getRooms()->add(new Room());

        $useCase = new DeleteRoomType($entityManager);

        self::assertFalse($useCase->execute($roomType));
    }

    public function testDeletesWhenUnused(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(RoomType::class));
        $entityManager->expects(self::once())->method('flush');

        $roomType = new RoomType();

        $useCase = new DeleteRoomType($entityManager);

        self::assertTrue($useCase->execute($roomType));
    }
}
