<?php

namespace App\UseCase\Organization;

use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteOrganization
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(Organization $organization): bool
    {
        $hasDependencies = $organization->getUsers()->count() > 0
            || $organization->getContacts()->count() > 0
            || $organization->getReservations()->count() > 0;

        if ($hasDependencies) {
            return false;
        }

        $this->entityManager->remove($organization);
        $this->entityManager->flush();

        return true;
    }
}
