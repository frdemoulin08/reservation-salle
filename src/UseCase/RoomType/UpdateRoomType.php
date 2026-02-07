<?php

namespace App\UseCase\RoomType;

use App\Entity\RoomType;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateRoomType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(RoomType $roomType): void
    {
        $this->entityManager->flush();
    }
}
