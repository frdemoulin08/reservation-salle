<?php

namespace App\Tests\Unit\UseCase\RoomType;

use App\Entity\RoomType;
use App\UseCase\RoomType\CreateRoomType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateRoomTypeTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(RoomType::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateRoomType($entityManager);

        $useCase->execute(new RoomType());
    }
}
