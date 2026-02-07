<?php

namespace App\Tests\Unit\UseCase\RoomLayout;

use App\Entity\RoomLayout;
use App\UseCase\RoomLayout\UpdateRoomLayout;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateRoomLayoutTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateRoomLayout($entityManager);

        $useCase->execute(new RoomLayout());
    }
}
