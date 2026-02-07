<?php

namespace App\UseCase\Room;

use Doctrine\ORM\EntityManagerInterface;

final class UpdateRoom
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(): void
    {
        $this->entityManager->flush();
    }
}
