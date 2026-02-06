<?php

namespace App\Reference;

final class OrganizationLegalNature
{
    /**
     * @var array<string, array{types: array<int, string>}>
     */
    private const OPTIONS = [
        'Association loi 1901' => ['types' => ['ASSOCIATION']],
        'Association reconnue d\'utilité publique' => ['types' => ['ASSOCIATION']],
        'Fondation' => ['types' => ['ASSOCIATION']],
        'Syndicat' => ['types' => ['ASSOCIATION']],
        'SARL' => ['types' => ['ENTREPRISE']],
        'SAS' => ['types' => ['ENTREPRISE']],
        'SA' => ['types' => ['ENTREPRISE']],
        'EI' => ['types' => ['ENTREPRISE']],
        'EURL' => ['types' => ['ENTREPRISE']],
        'Auto-entrepreneur' => ['types' => ['ENTREPRISE']],
        'Commune' => ['types' => ['COLLECTIVITE']],
        'Département' => ['types' => ['COLLECTIVITE']],
        'Région' => ['types' => ['COLLECTIVITE']],
        'EPCI' => ['types' => ['COLLECTIVITE']],
        'Établissement public' => ['types' => ['COLLECTIVITE', 'AUTRE']],
        'Conseil départemental des Ardennes' => ['types' => ['CD08_SERVICE']],
        'Autre' => ['types' => ['AUTRE']],
    ];

    /**
     * @return array<string, string>
     */
    public static function choices(): array
    {
        $choices = [];
        foreach (self::OPTIONS as $label => $meta) {
            $choices[$label] = $label;
        }

        return $choices;
    }

    /**
     * @return array<int, string>
     */
    public static function typesFor(?string $value): array
    {
        if (null === $value || '' === $value) {
            return [];
        }

        return self::OPTIONS[$value]['types'] ?? [];
    }

    public static function isAllowed(?string $organizationType, ?string $value): bool
    {
        if (null === $value || '' === $value) {
            return true;
        }

        if (null === $organizationType || '' === $organizationType) {
            return false;
        }

        return in_array($organizationType, self::typesFor($value), true);
    }
}
