<?php

namespace App\UseCase\EquipmentType;

use App\Entity\EquipmentType;
use Doctrine\ORM\EntityManagerInterface;

final class CreateEquipmentType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(EquipmentType $equipmentType): void
    {
        $this->entityManager->persist($equipmentType);
        $this->entityManager->flush();
    }
}
