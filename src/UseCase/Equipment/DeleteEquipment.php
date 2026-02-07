<?php

namespace App\UseCase\Equipment;

use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteEquipment
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Equipment $equipment): void
    {
        $this->entityManager->remove($equipment);
        $this->entityManager->flush();
    }
}
