<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\VenueDocument;
use App\Service\SiteDocumentStorage;
use App\UseCase\Venue\DeleteVenueDocument;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class DeleteVenueDocumentTest extends TestCase
{
    public function testDeletesDocument(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('remove')->with(self::isInstanceOf(VenueDocument::class));
        $entityManager->expects(self::once())->method('flush');

        $storage = $this->createMock(SiteDocumentStorage::class);
        $storage->expects(self::once())
            ->method('delete')
            ->with('documents/file.pdf', true);

        $useCase = new DeleteVenueDocument($entityManager, $storage);

        $document = new VenueDocument();
        $document->setFilePath('documents/file.pdf');
        $document->setIsPublic(true);

        $useCase->execute($document);
    }
}
