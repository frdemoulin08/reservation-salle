<?php

namespace App\UseCase\Room;

use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteRoom
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Room $room): bool
    {
        $hasDependencies = $room->getRoomEquipments()->count() > 0
            || $room->getRoomServices()->count() > 0
            || $room->getRoomDocuments()->count() > 0
            || $room->getRoomPricings()->count() > 0
            || $room->getReservations()->count() > 0;

        if ($hasDependencies) {
            return false;
        }

        $this->entityManager->remove($room);
        $this->entityManager->flush();

        return true;
    }
}
