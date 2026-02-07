<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\Venue;
use App\UseCase\Venue\DeleteVenue;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteVenueTest extends TestCase
{
    public function testRemovesAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(Venue::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new DeleteVenue($entityManager);

        $useCase->execute(new Venue());
    }
}
