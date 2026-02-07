<?php

namespace App\UseCase\RoomType;

use App\Entity\RoomType;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteRoomType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(RoomType $roomType): bool
    {
        if ($roomType->getRooms()->count() > 0) {
            return false;
        }

        $this->entityManager->remove($roomType);
        $this->entityManager->flush();

        return true;
    }
}
