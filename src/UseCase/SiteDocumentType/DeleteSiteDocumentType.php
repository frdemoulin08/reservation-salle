<?php

namespace App\UseCase\SiteDocumentType;

use App\Entity\SiteDocumentType;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteSiteDocumentType
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function execute(SiteDocumentType $documentType): bool
    {
        if ($documentType->getDocuments()->count() > 0) {
            return false;
        }

        $this->entityManager->remove($documentType);
        $this->entityManager->flush();

        return true;
    }
}
