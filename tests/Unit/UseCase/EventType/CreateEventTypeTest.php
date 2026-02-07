<?php

namespace App\Tests\Unit\UseCase\EventType;

use App\Entity\EventType;
use App\UseCase\EventType\CreateEventType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateEventTypeTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(EventType::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateEventType($entityManager);

        $useCase->execute(new EventType());
    }
}
