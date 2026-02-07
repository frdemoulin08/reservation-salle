<?php

namespace App\UseCase\Country;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;

final class CreateCountry
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Country $country): void
    {
        $this->entityManager->persist($country);
        $this->entityManager->flush();
    }
}
