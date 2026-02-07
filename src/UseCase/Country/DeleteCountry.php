<?php

namespace App\UseCase\Country;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteCountry
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Country $country): void
    {
        $this->entityManager->remove($country);
        $this->entityManager->flush();
    }
}
