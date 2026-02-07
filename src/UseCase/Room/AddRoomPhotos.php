<?php

namespace App\UseCase\Room;

use App\Entity\Room;
use App\Entity\RoomDocument;
use App\Service\PhotoUploadHelper;
use App\Service\RoomDocumentStorage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class AddRoomPhotos
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RoomDocumentStorage $documentStorage,
        private readonly PhotoUploadHelper $photoUploadHelper,
    ) {
    }

    /**
     * @param array<int, UploadedFile> $files
     *
     * @return array<int, RoomDocument>
     */
    public function execute(Room $room, array $files): array
    {
        $createdDocuments = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
            $relativePath = $this->documentStorage->storeUploadedFile($room, $file, 'photos', true);
            $label = $this->photoUploadHelper->createDefaultLabel($file);

            $photoDocument = (new RoomDocument())
                ->setRoom($room)
                ->setLabel($label)
                ->setFilePath($relativePath)
                ->setMimeType($mimeType)
                ->setType(RoomDocument::TYPE_PHOTO);

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
