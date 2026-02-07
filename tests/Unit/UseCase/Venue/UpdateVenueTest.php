<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\Venue;
use App\UseCase\Venue\UpdateVenue;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateVenueTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateVenue($entityManager);

        $useCase->execute(new Venue());
    }
}
