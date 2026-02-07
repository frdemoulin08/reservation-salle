<?php

namespace App\Tests\Unit\UseCase\Country;

use App\Entity\Country;
use App\UseCase\Country\CreateCountry;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CreateCountryTest extends TestCase
{
    public function testPersistsAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(Country::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new CreateCountry($entityManager);

        $useCase->execute(new Country());
    }
}
