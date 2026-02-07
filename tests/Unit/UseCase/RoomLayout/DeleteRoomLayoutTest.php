<?php

namespace App\Tests\Unit\UseCase\RoomLayout;

use App\Entity\Room;
use App\Entity\RoomLayout;
use App\UseCase\RoomLayout\DeleteRoomLayout;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteRoomLayoutTest extends TestCase
{
    public function testRejectsWhenUsed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $roomLayout = new RoomLayout();
        $roomLayout->getRooms()->add(new Room());

        $useCase = new DeleteRoomLayout($entityManager);

        self::assertFalse($useCase->execute($roomLayout));
    }

    public function testDeletesWhenUnused(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(RoomLayout::class));
        $entityManager->expects(self::once())->method('flush');

        $roomLayout = new RoomLayout();

        $useCase = new DeleteRoomLayout($entityManager);

        self::assertTrue($useCase->execute($roomLayout));
    }
}
