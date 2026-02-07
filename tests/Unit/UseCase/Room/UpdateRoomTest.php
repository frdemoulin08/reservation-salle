<?php

namespace App\Tests\Unit\UseCase\Room;

use App\Entity\Room;
use App\UseCase\Room\UpdateRoom;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateRoomTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateRoom($entityManager);

        $useCase->execute(new Room());
    }
}
