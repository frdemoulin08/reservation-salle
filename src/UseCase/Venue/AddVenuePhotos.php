<?php

namespace App\UseCase\Venue;

use App\Entity\SiteDocumentType;
use App\Entity\Venue;
use App\Entity\VenueDocument;
use App\Service\PhotoUploadHelper;
use App\Service\SiteDocumentStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AddVenuePhotos
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteDocumentStorage $documentStorage,
        private readonly PhotoUploadHelper $photoUploadHelper,
    ) {
    }

    /**
     * @param array<int, UploadedFile> $files
     *
     * @return array<int, VenueDocument>
     */
    public function execute(Venue $venue, SiteDocumentType $photoType, array $files): array
    {
        $createdDocuments = [];
        $isPublic = $photoType->isPublic();

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $size = $file->getSize();
            $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
            $relativePath = $this->documentStorage->storeUploadedFile($venue, $file, 'photos', $isPublic);
            $label = $this->photoUploadHelper->createDefaultLabel($file);

            $photoDocument = (new VenueDocument())
                ->setVenue($venue)
                ->setLabel($label)
                ->setFilePath($relativePath)
                ->setMimeType($mimeType)
                ->setOriginalFilename($file->getClientOriginalName())
                ->setSize($size)
                ->setIsPublic($isPublic)
                ->setDocumentType($photoType);

            $this->entityManager->persist($photoDocument);
            $createdDocuments[] = $photoDocument;
        }

        if ([] === $createdDocuments) {
            return [];
        }

        $this->entityManager->flush();

        return $createdDocuments;
    }
}
