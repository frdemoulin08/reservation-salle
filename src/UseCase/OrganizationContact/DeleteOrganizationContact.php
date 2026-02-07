<?php

namespace App\UseCase\OrganizationContact;

use App\Entity\OrganizationContact;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteOrganizationContact
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(OrganizationContact $contact): bool
    {
        $reservationsCount = $this->entityManager->getRepository(Reservation::class)->count([
            'organizationContact' => $contact,
        ]);
        if ($reservationsCount > 0) {
            return false;
        }

        $this->entityManager->remove($contact);
        $this->entityManager->flush();

        return true;
    }
}
