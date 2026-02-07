<?php

namespace App\UseCase\Equipment;

use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;

final class CreateEquipment
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Equipment $equipment): void
    {
        $this->entityManager->persist($equipment);
        $this->entityManager->flush();
    }
}
