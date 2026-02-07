<?php

namespace App\UseCase\Equipment;

use App\Entity\Equipment;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateEquipment
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Equipment $equipment): void
    {
        $this->entityManager->flush();
    }
}
