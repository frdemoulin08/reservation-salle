<?php

namespace App\UseCase\RoomType;

use App\Entity\RoomType;
use Doctrine\ORM\EntityManagerInterface;

final class CreateRoomType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(RoomType $roomType): void
    {
        $this->entityManager->persist($roomType);
        $this->entityManager->flush();
    }
}
