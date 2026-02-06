<?php

namespace App\Service;

final class CompanyLookupResult
{
    public const STATUS_OK = 'ok';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_UNAVAILABLE = 'unavailable';

    /**
     * @param array<string, mixed>|null $data
     */
    public function __construct(
        public readonly string $status,
        public readonly ?array $data = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function ok(array $data): self
    {
        return new self(self::STATUS_OK, $data);
    }

    public static function notFound(): self
    {
        return new self(self::STATUS_NOT_FOUND);
    }

    public static function unavailable(): self
    {
        return new self(self::STATUS_UNAVAILABLE);
    }
}
