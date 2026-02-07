<?php

namespace App\UseCase\Venue;

use App\Entity\Venue;
use Doctrine\ORM\EntityManagerInterface;

final class CreateVenue
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Venue $venue): void
    {
        $this->entityManager->persist($venue);
        $this->entityManager->flush();
    }
}
