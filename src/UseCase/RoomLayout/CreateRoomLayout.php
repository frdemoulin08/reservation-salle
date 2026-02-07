<?php

namespace App\UseCase\RoomLayout;

use App\Entity\RoomLayout;
use Doctrine\ORM\EntityManagerInterface;

final class CreateRoomLayout
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(RoomLayout $roomLayout): void
    {
        $this->entityManager->persist($roomLayout);
        $this->entityManager->flush();
    }
}
