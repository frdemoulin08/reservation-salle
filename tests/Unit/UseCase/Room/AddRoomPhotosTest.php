<?php

namespace App\Tests\Unit\UseCase\Room;

use App\Entity\Room;
use App\Entity\RoomDocument;
use App\Service\PhotoUploadHelper;
use App\Service\RoomDocumentStorage;
use App\UseCase\Room\AddRoomPhotos;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AddRoomPhotosTest extends TestCase
{
    public function testPersistsDocuments(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::exactly(2))
            ->method('persist')
            ->with(self::isInstanceOf(RoomDocument::class));
        $entityManager->expects(self::once())->method('flush');

        $storage = $this->createMock(RoomDocumentStorage::class);
        $storage->expects(self::exactly(2))
            ->method('storeUploadedFile')
            ->willReturnCallback(function (Room $room, UploadedFile $file, string $folder, bool $isPublic): string {
                static $call = 0;
                ++$call;

                self::assertSame('photos', $folder);
                self::assertTrue($isPublic);

                return 1 === $call ? 'photos/one.jpg' : 'photos/two.jpg';
            });

        $helper = $this->createStub(PhotoUploadHelper::class);
        $helper->method('createDefaultLabel')->willReturnOnConsecutiveCalls('Photo 1', 'Photo 2');

        $useCase = new AddRoomPhotos($entityManager, $storage, $helper);

        $room = new Room();
        $fileOne = $this->createUploadedFile('one.jpg');
        $fileTwo = $this->createUploadedFile('two.jpg');

        $documents = $useCase->execute($room, [$fileOne, $fileTwo]);

        self::assertCount(2, $documents);
        self::assertSame('Photo 1', $documents[0]->getLabel());
        self::assertSame('photos/one.jpg', $documents[0]->getFilePath());
        self::assertSame('Photo 2', $documents[1]->getLabel());
        self::assertSame('photos/two.jpg', $documents[1]->getFilePath());
    }

    public function testSkipsWhenNoFiles(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::never())->method('flush');

        $storage = $this->createStub(RoomDocumentStorage::class);
        $helper = $this->createStub(PhotoUploadHelper::class);

        $useCase = new AddRoomPhotos($entityManager, $storage, $helper);

        $documents = $useCase->execute(new Room(), []);

        self::assertSame([], $documents);
    }

    private function createUploadedFile(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'room_photo_');
        file_put_contents($path, 'content');

        return new UploadedFile($path, $name, 'image/jpeg', null, true);
    }
}
