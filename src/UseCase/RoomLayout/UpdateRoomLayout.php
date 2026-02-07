<?php

namespace App\UseCase\RoomLayout;

use App\Entity\RoomLayout;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateRoomLayout
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(RoomLayout $roomLayout): void
    {
        $this->entityManager->flush();
    }
}
