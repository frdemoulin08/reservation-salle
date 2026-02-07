<?php

namespace App\UseCase\Venue;

use App\Entity\VenueDocument;
use App\Service\SiteDocumentStorage;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteVenuePhoto
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteDocumentStorage $documentStorage,
    ) {
    }

    public function execute(VenueDocument $document): void
    {
        $this->documentStorage->delete($document->getFilePath(), $document->isPublic());
        $this->entityManager->remove($document);
        $this->entityManager->flush();
    }
}
