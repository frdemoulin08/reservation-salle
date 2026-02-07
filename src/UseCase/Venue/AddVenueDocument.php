<?php

namespace App\UseCase\Venue;

use App\Entity\SiteDocumentType;
use App\Entity\Venue;
use App\Entity\VenueDocument;
use App\Service\SiteDocumentStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AddVenueDocument
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteDocumentStorage $documentStorage,
    ) {
    }

    public function execute(
        Venue $venue,
        VenueDocument $document,
        SiteDocumentType $documentType,
        UploadedFile $uploadedFile,
    ): VenueDocument {
        $originalName = $uploadedFile->getClientOriginalName();
        $size = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType() ?? $uploadedFile->getClientMimeType();
        $relativePath = $this->documentStorage->storeUploadedFile($venue, $uploadedFile, 'documents', $documentType->isPublic());

        $label = trim($document->getLabel());
        if ('' === $label) {
            $label = pathinfo($originalName, PATHINFO_FILENAME) ?: 'Document';
            $document->setLabel($label);
        }

        $document
            ->setVenue($venue)
            ->setDocumentType($documentType)
            ->setFilePath($relativePath)
            ->setMimeType($mimeType)
            ->setOriginalFilename($originalName)
            ->setSize($size)
            ->setIsPublic($documentType->isPublic());

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return $document;
    }
}
