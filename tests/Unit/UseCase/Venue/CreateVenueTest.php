<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\Venue;
use App\UseCase\Venue\CreateVenue;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateVenueTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(Venue::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateVenue($entityManager);

        $useCase->execute(new Venue());
    }
}
