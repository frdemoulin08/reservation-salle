<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\SiteDocumentType;
use App\Entity\Venue;
use App\Entity\VenueDocument;
use App\Service\SiteDocumentStorage;
use App\UseCase\Venue\AddVenueDocument;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AddVenueDocumentTest extends TestCase
{
    public function testAddsDocumentWithDefaultLabel(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(VenueDocument::class));
        $entityManager->expects(self::once())->method('flush');

        $storage = $this->createMock(SiteDocumentStorage::class);
        $storage->expects(self::once())
            ->method('storeUploadedFile')
            ->with(self::isInstanceOf(Venue::class), self::isInstanceOf(UploadedFile::class), 'documents', true)
            ->willReturn('documents/report.pdf');

        $useCase = new AddVenueDocument($entityManager, $storage);

        $venue = new Venue();
        $documentType = (new SiteDocumentType())->setIsPublic(true);
        $document = new VenueDocument();
        $document->setLabel('');

        $file = $this->createUploadedFile('report.pdf', 'application/pdf');

        $useCase->execute($venue, $document, $documentType, $file);

        self::assertSame('report', $document->getLabel());
        self::assertSame('documents/report.pdf', $document->getFilePath());
        self::assertSame('report.pdf', $document->getOriginalFilename());
        self::assertSame($documentType, $document->getDocumentType());
        self::assertSame($venue, $document->getVenue());
    }

    private function createUploadedFile(string $name, string $mimeType): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'venue_doc_');
        file_put_contents($path, 'content');

        return new UploadedFile($path, $name, $mimeType, null, true);
    }
}
