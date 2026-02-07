<?php

namespace App\UseCase\EventType;

use App\Entity\EventType;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateEventType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(EventType $eventType): void
    {
        $this->entityManager->flush();
    }
}
