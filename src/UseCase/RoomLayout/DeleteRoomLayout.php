<?php

namespace App\UseCase\RoomLayout;

use App\Entity\RoomLayout;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteRoomLayout
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(RoomLayout $roomLayout): bool
    {
        if ($roomLayout->getRooms()->count() > 0) {
            return false;
        }

        $this->entityManager->remove($roomLayout);
        $this->entityManager->flush();

        return true;
    }
}
