<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PhotoUploadHelper
{
    /**
     * @var string[]
     */
    public const MIME_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public const MAX_SIZE = '2M';

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @return array<int, object>
     */
    public function buildFormConstraints(): array
    {
        return [
            new Count(
                min: 1,
                minMessage: 'photo.upload.min_files'
            ),
            new All([
                $this->buildImageConstraint(),
            ]),
        ];
    }

    public function buildImageConstraint(): Image
    {
        return new Image(
            maxSize: self::MAX_SIZE,
            mimeTypes: self::MIME_TYPES,
            mimeTypesMessage: 'photo.upload.invalid_mime',
            maxSizeMessage: 'photo.upload.max_size',
        );
    }

    /**
     * @return UploadedFile[]
     */
    public function normalizeFiles(mixed $files): array
    {
        if (!$files) {
            return [];
        }

        $files = is_array($files) ? $files : [$files];

        return array_values(array_filter(
            $files,
            static fn (mixed $file) => $file instanceof UploadedFile
        ));
    }

    public function validateFiles(array $files): ?string
    {
        $constraint = $this->buildImageConstraint();

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $violations = $this->validator->validate($file, $constraint);
            if (count($violations) > 0) {
                return (string) $violations[0]->getMessage();
            }
        }

        return null;
    }

    public function createDefaultLabel(UploadedFile $file): string
    {
        $originalName = $file->getClientOriginalName();

        return pathinfo($originalName, PATHINFO_FILENAME) ?: 'Photo';
    }

    public function getNoFilesMessage(): string
    {
        return $this->translator->trans('photo.upload.no_files', domain: 'validators');
    }

    public function getSinglePhotoNotAllowedMessage(): string
    {
        return $this->translator->trans('photo.upload.single_not_allowed', domain: 'validators');
    }

    public function getPhotosAddedMessage(): string
    {
        return $this->translator->trans('photo.upload.success', domain: 'validators');
    }

    public function getPhotosAddedShortMessage(): string
    {
        return $this->translator->trans('photo.upload.success_short', domain: 'validators');
    }

    public function getPhotoTypeMissingMessage(): string
    {
        return $this->translator->trans('photo.type.missing', domain: 'validators');
    }

    public function getPhotoTypeNotFoundMessage(): string
    {
        return $this->translator->trans('photo.type.not_found', domain: 'validators');
    }

    public function getInvalidCsrfMessage(): string
    {
        return $this->translator->trans('photo.security.invalid_csrf', domain: 'validators');
    }

    public function getPhotoNotFoundMessage(): string
    {
        return $this->translator->trans('photo.not_found', domain: 'validators');
    }

    public function getVenueNotFoundMessage(): string
    {
        return $this->translator->trans('photo.context.venue_not_found', domain: 'validators');
    }

    public function getRoomNotFoundMessage(): string
    {
        return $this->translator->trans('photo.context.room_not_found', domain: 'validators');
    }

    public function getPhotoDeletedMessage(): string
    {
        return $this->translator->trans('photo.delete.success', domain: 'validators');
    }

    public function getLabelRequiredMessage(): string
    {
        return $this->translator->trans('photo.label.required', domain: 'validators');
    }

    public function getLabelTooLongMessage(): string
    {
        return $this->translator->trans('photo.label.too_long', domain: 'validators');
    }
}
