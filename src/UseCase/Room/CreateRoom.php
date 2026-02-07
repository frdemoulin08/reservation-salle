<?php

namespace App\UseCase\Room;

use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;

final class CreateRoom
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Room $room): void
    {
        $this->entityManager->persist($room);
        $this->entityManager->flush();
    }
}
