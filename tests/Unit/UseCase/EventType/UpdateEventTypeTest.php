<?php

namespace App\Tests\Unit\UseCase\EventType;

use App\Entity\EventType;
use App\UseCase\EventType\UpdateEventType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateEventTypeTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateEventType($entityManager);

        $useCase->execute(new EventType());
    }
}
