<?php

namespace App\UseCase\Venue;

use App\Entity\Venue;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteVenue
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Venue $venue): void
    {
        $this->entityManager->remove($venue);
        $this->entityManager->flush();
    }
}
