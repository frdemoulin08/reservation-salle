<?php

namespace App\Tests\Unit\UseCase\Venue;

use App\Entity\VenueDocument;
use App\Service\PhotoUploadHelper;
use App\UseCase\Venue\UpdateVenuePhotoLabel;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateVenuePhotoLabelTest extends TestCase
{
    public function testRejectsEmptyLabel(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $useCase = new UpdateVenuePhotoLabel($entityManager, $this->createHelper());
        $document = new VenueDocument();

        $message = $useCase->execute($document, '   ');

        self::assertSame('photo.label.required', $message);
    }

    public function testRejectsTooLongLabel(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $useCase = new UpdateVenuePhotoLabel($entityManager, $this->createHelper());
        $document = new VenueDocument();

        $message = $useCase->execute($document, str_repeat('a', 256));

        self::assertSame('photo.label.too_long', $message);
    }

    public function testUpdatesLabel(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $useCase = new UpdateVenuePhotoLabel($entityManager, $this->createHelper());
        $document = new VenueDocument();
        $document->setLabel('Ancien');

        $message = $useCase->execute($document, 'Nouvelle');

        self::assertNull($message);
        self::assertSame('Nouvelle', $document->getLabel());
    }

    private function createHelper(): PhotoUploadHelper
    {
        $validator = $this->createStub(ValidatorInterface::class);
        $translator = $this->createStub(TranslatorInterface::class);
        $translator->method('trans')->willReturnCallback(static fn (string $id): string => $id);

        return new PhotoUploadHelper($validator, $translator);
    }
}
