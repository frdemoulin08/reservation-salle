<?php

namespace App\UseCase\EventType;

use App\Entity\EventType;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteEventType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(EventType $eventType): bool
    {
        if ($eventType->getReservations()->count() > 0) {
            return false;
        }

        $this->entityManager->remove($eventType);
        $this->entityManager->flush();

        return true;
    }
}
