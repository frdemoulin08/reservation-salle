<?php

namespace App\Tests\Unit\UseCase\Country;

use App\Entity\Country;
use App\UseCase\Country\UpdateCountry;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UpdateCountryTest extends TestCase
{
    public function testFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateCountry($entityManager);

        $useCase->execute(new Country());
    }
}
