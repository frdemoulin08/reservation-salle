<?php

namespace App\Tests\Unit\UseCase\EventType;

use App\Entity\EventType;
use App\Entity\Reservation;
use App\UseCase\EventType\DeleteEventType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteEventTypeTest extends TestCase
{
    public function testRejectsWhenUsed(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::never())->method('flush');

        $eventType = new EventType();
        $eventType->getReservations()->add(new Reservation());

        $useCase = new DeleteEventType($entityManager);

        self::assertFalse($useCase->execute($eventType));
    }

    public function testDeletesWhenUnused(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(EventType::class));
        $entityManager->expects(self::once())->method('flush');

        $eventType = new EventType();

        $useCase = new DeleteEventType($entityManager);

        self::assertTrue($useCase->execute($eventType));
    }
}
