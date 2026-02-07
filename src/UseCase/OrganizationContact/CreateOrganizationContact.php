<?php

namespace App\UseCase\OrganizationContact;

use App\Entity\OrganizationContact;
use Doctrine\ORM\EntityManagerInterface;

final class CreateOrganizationContact
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(OrganizationContact $contact): void
    {
        $this->entityManager->persist($contact);
        $this->entityManager->flush();
    }
}
