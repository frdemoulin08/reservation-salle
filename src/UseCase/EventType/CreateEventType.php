<?php

namespace App\UseCase\EventType;

use App\Entity\EventType;
use Doctrine\ORM\EntityManagerInterface;

final class CreateEventType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(EventType $eventType): void
    {
        $this->entityManager->persist($eventType);
        $this->entityManager->flush();
    }
}
