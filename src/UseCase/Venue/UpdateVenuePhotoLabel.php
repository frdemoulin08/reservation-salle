<?php

namespace App\UseCase\Venue;

use App\Entity\VenueDocument;
use App\Service\PhotoUploadHelper;
use Doctrine\ORM\EntityManagerInterface;

final class UpdateVenuePhotoLabel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PhotoUploadHelper $photoUploadHelper,
    ) {
    }

    public function execute(VenueDocument $document, string $label): ?string
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
