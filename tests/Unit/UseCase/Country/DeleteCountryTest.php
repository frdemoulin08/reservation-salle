<?php

namespace App\Tests\Unit\UseCase\Country;

use App\Entity\Country;
use App\UseCase\Country\DeleteCountry;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteCountryTest extends TestCase
{
    public function testRemovesAndFlushes(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(Country::class));
        $entityManager->expects(self::once())->method('flush');

        $useCase = new DeleteCountry($entityManager);

        $useCase->execute(new Country());
    }
}
