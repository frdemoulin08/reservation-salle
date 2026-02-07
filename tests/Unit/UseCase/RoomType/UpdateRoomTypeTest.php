<?php

namespace App\Tests\Unit\UseCase\RoomType;

use App\Entity\RoomType;
use App\UseCase\RoomType\UpdateRoomType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateRoomTypeTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateRoomType($entityManager);

        $useCase->execute(new RoomType());
    }
}
