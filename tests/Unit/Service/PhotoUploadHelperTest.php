<?php

namespace App\Tests\Unit\Service;

use App\Service\PhotoUploadHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhotoUploadHelperTest extends TestCase
{
    public function testValidateFilesReturnsInvalidMimeMessage(): void
    {
        $helper = $this->createHelper();
        $path = $this->createTempFile('not-an-image');

        try {
            $uploadedFile = new UploadedFile($path, 'document.txt', 'text/plain', null, true);

            $message = $helper->validateFiles([$uploadedFile]);

            self::assertSame('photo.upload.invalid_mime', $message);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testValidateFilesReturnsMaxSizeMessage(): void
    {
        $helper = $this->createHelper();
        $path = $this->createTempFile(str_repeat('a', 6 * 1024 * 1024));

        try {
            $uploadedFile = new UploadedFile($path, 'grand.png', 'image/png', null, true);

            $message = $helper->validateFiles([$uploadedFile]);

            self::assertSame('photo.upload.max_size', $message);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function testCreateDefaultLabelStripsExtension(): void
    {
        $helper = $this->createHelper();
        $path = $this->createTempFile('image');

        try {
            $uploadedFile = new UploadedFile($path, 'photo-de-test.png', 'image/png', null, true);

            $label = $helper->createDefaultLabel($uploadedFile);

            self::assertSame('photo-de-test', $label);
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function createHelper(): PhotoUploadHelper
    {
        $validator = Validation::createValidator();
        $translator = new class implements TranslatorInterface {
            public function trans($id, array $parameters = [], $domain = null, $locale = null): string
            {
                return (string) $id;
            }

            public function getLocale(): string
            {
                return 'fr';
            }

            public function setLocale($locale): void
            {
            }
        };

        return new PhotoUploadHelper($validator, $translator);
    }

    private function createTempFile(string $contents): string
    {
        $path = tempnam(sys_get_temp_dir(), 'photo_helper_');
        if (false === $path) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire.');
        }

        file_put_contents($path, $contents);

        return $path;
    }
}
