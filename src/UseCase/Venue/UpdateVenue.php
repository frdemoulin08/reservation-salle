<?php

namespace App\UseCase\Venue;

use Doctrine\ORM\EntityManagerInterface;

final class UpdateVenue
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(): void
    {
        $this->entityManager->flush();
    }
}
