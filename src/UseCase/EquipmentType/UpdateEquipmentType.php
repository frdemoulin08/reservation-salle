<?php

namespace App\UseCase\EquipmentType;

use App\Entity\EquipmentType;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateEquipmentType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(EquipmentType $equipmentType): void
    {
        $this->entityManager->flush();
    }
}
