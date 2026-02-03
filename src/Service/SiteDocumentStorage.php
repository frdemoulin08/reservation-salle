<?php

namespace App\Service;

use App\Entity\Venue;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class SiteDocumentStorage
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
        Venue $venue,
        UploadedFile $file,
        string $category,
        bool $isPublic,
    ): string {
        $path = $this->buildPath($venue, $category, $file);
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

    private function buildPath(Venue $venue, string $category, UploadedFile $file): string
    {
        $category = trim($category, '/');
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();
        $extension = $extension ? '.'.strtolower($extension) : '';

        return sprintf(
            '%s/venues/%s/%s/%s%s',
            self::PUBLIC_BASE_PATH,
            $venue->getPublicIdentifier(),
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
