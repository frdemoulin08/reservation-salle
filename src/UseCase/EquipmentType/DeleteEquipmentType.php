<?php

namespace App\UseCase\EquipmentType;

use App\Entity\EquipmentType;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteEquipmentType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(EquipmentType $equipmentType): bool
    {
        if ($equipmentType->getRoomEquipments()->count() > 0
            || $equipmentType->getVenueEquipments()->count() > 0
            || $equipmentType->getEquipments()->count() > 0
        ) {
            return false;
        }

        $this->entityManager->remove($equipmentType);
        $this->entityManager->flush();

        return true;
    }
}
