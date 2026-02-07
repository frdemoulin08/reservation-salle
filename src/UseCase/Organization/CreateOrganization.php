<?php

namespace App\UseCase\Organization;

use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;

final class CreateOrganization
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly EnrichOrganizationFromSiret $enrichOrganizationFromSiret,
    ) {
    }

    public function execute(Organization $organization): void
    {
        $this->enrichOrganizationFromSiret->enrich($organization);
        $this->entityManager->persist($organization);
        $this->entityManager->flush();
    }
}
