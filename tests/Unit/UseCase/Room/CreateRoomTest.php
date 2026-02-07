<?php

namespace App\Tests\Unit\UseCase\Room;

use App\Entity\Room;
use App\UseCase\Room\CreateRoom;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateRoomTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(Room::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateRoom($entityManager);

        $useCase->execute(new Room());
    }
}
