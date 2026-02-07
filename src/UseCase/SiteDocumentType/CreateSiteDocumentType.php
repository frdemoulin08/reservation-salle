<?php

namespace App\UseCase\SiteDocumentType;

use App\Entity\SiteDocumentType;
use Doctrine\ORM\EntityManagerInterface;

final class CreateSiteDocumentType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(SiteDocumentType $documentType): void
    {
        $this->entityManager->persist($documentType);
        $this->entityManager->flush();
    }
}
