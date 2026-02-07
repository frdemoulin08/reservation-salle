<?php

namespace App\UseCase\Room;

use App\Entity\RoomDocument;
use App\Service\PhotoUploadHelper;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateRoomPhotoLabel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PhotoUploadHelper $photoUploadHelper,
    ) {
    }

    public function execute(RoomDocument $document, string $label): ?string
    {
        $normalized = trim($label);
        if ('' === $normalized) {
            return $this->photoUploadHelper->getLabelRequiredMessage();
        }

        if (mb_strlen($normalized) > 255) {
            return $this->photoUploadHelper->getLabelTooLongMessage();
        }

        $document->setLabel($normalized);
        $this->entityManager->flush();

        return null;
    }
}
