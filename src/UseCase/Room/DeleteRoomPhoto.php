<?php

namespace App\UseCase\Room;

use App\Entity\RoomDocument;
use App\Service\RoomDocumentStorage;
use Doctrine\ORM\EntityManagerInterface;

final class DeleteRoomPhoto
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RoomDocumentStorage $documentStorage,
    ) {
    }

    public function execute(RoomDocument $document): void
    {
        $this->documentStorage->delete($document->getFilePath(), true);
        $this->entityManager->remove($document);
        $this->entityManager->flush();
    }
}
