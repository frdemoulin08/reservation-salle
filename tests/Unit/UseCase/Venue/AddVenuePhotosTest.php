<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\SiteDocumentType;
use App\Entity\Venue;
use App\Entity\VenueDocument;
use App\Service\PhotoUploadHelper;
use App\Service\SiteDocumentStorage;
use App\UseCase\Venue\AddVenuePhotos;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AddVenuePhotosTest extends TestCase
{
    public function testPersistsDocuments(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(2))
            ->method('persist')
            ->with(self::isInstanceOf(VenueDocument::class));
        $entityManager->expects(self::once())->method('flush');

        $storage = $this->createMock(SiteDocumentStorage::class);
        $storage->expects(self::exactly(2))
            ->method('storeUploadedFile')
            ->willReturnCallback(function (Venue $venue, UploadedFile $file, string $folder, bool $isPublic): string {
                static $call = 0;
                ++$call;

                self::assertSame('photos', $folder);
                self::assertFalse($isPublic);

                return 1 === $call ? 'photos/one.jpg' : 'photos/two.jpg';
            });

        $helper = $this->createStub(PhotoUploadHelper::class);
        $helper->method('createDefaultLabel')->willReturnOnConsecutiveCalls('Photo 1', 'Photo 2');

        $useCase = new AddVenuePhotos($entityManager, $storage, $helper);

        $venue = new Venue();
        $photoType = (new SiteDocumentType())->setIsPublic(false);

        $fileOne = $this->createUploadedFile('one.jpg');
        $fileTwo = $this->createUploadedFile('two.jpg');

        $documents = $useCase->execute($venue, $photoType, [$fileOne, $fileTwo]);

        self::assertCount(2, $documents);
        self::assertSame('Photo 1', $documents[0]->getLabel());
        self::assertSame('photos/one.jpg', $documents[0]->getFilePath());
        self::assertFalse($documents[0]->isPublic());
        self::assertSame($photoType, $documents[0]->getDocumentType());
    }

    public function testSkipsWhenNoFiles(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $storage = $this->createStub(SiteDocumentStorage::class);
        $helper = $this->createStub(PhotoUploadHelper::class);

        $useCase = new AddVenuePhotos($entityManager, $storage, $helper);

        $documents = $useCase->execute(new Venue(), new SiteDocumentType(), []);

        self::assertSame([], $documents);
    }

    private function createUploadedFile(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'venue_photo_');
        file_put_contents($path, 'content');

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }
}
