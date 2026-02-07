<?php

namespace App\Tests\Unit\UseCase\RoomLayout;

use App\Entity\RoomLayout;
use App\UseCase\RoomLayout\CreateRoomLayout;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateRoomLayoutTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(RoomLayout::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateRoomLayout($entityManager);

        $useCase->execute(new RoomLayout());
    }
}
