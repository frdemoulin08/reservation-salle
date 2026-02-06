<?php

namespace App\Service;

use App\Entity\Room;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class RoomDocumentStorage
{
    public const PUBLIC_BASE_PATH = 'uploads';

    public function __construct(
        private readonly FilesystemOperator $publicFilesystem,
        private readonly FilesystemOperator $privateFilesystem,
    ) {
    }

    /**
     * @throws FilesystemException
     */
    public function storeUploadedFile(
        Room $room,
        UploadedFile $file,
        string $category,
        bool $isPublic,
    ): string {
        $path = $this->buildPath($room, $category, $file);
        $stream = fopen($file->getPathname(), 'r');
        if (false === $stream) {
            throw new \RuntimeException('Impossible de lire le fichier uploadé.');
        }

        try {
            $filesystem = $this->getFilesystem($isPublic);
            $filesystem->createDirectory(pathinfo($path, PATHINFO_DIRNAME));
            $filesystem->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return $path;
    }

    /**
     * @throws FilesystemException
     */
    public function delete(string $path, bool $isPublic): void
    {
        $filesystem = $this->getFilesystem($isPublic);
        if ($filesystem->fileExists($path)) {
            $filesystem->delete($path);
        }
    }

    /**
     * @return resource
     *
     * @throws FilesystemException
     */
    public function readStream(string $path, bool $isPublic)
    {
        $filesystem = $this->getFilesystem($isPublic);

        return $filesystem->readStream($path);
    }

    private function buildPath(Room $room, string $category, UploadedFile $file): string
    {
        $publicIdentifier = $room->getPublicIdentifier();
        if ('' === $publicIdentifier) {
            throw new \RuntimeException('Impossible de stocker un document sans identifiant public de salle.');
        }

        $category = trim($category, '/');
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $extension = $extension ? '.'.strtolower($extension) : '';

        return sprintf(
            '%s/rooms/%s/%s/%s%s',
            self::PUBLIC_BASE_PATH,
            $publicIdentifier,
            $category,
            Uuid::v4()->toRfc4122(),
            $extension
        );
    }

    private function getFilesystem(bool $isPublic): FilesystemOperator
    {
        return $isPublic ? $this->publicFilesystem : $this->privateFilesystem;
    }
}
