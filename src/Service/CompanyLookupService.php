<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CompanyLookupService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
    ) {
    }

    public function normalizeSiret(string $siret): string
    {
        return preg_replace('/\D+/', '', $siret) ?? '';
    }

    public function isValidSiret(string $siret): bool
    {
        $normalized = $this->normalizeSiret($siret);
        if (!preg_match('/^\d{14}$/', $normalized)) {
            return false;
        }

        return $this->passesLuhn($normalized);
    }

    public function findBySiret(string $siret): CompanyLookupResult
    {
        $normalized = $this->normalizeSiret($siret);
        if ('' === $normalized) {
            return CompanyLookupResult::notFound();
        }

        try {
            $response = $this->httpClient->request('GET', rtrim($this->baseUrl, '/').'/search', [
                'headers' => ['Accept' => 'application/json'],
                'query' => [
                    'q' => $normalized,
                    'per_page' => 1,
                ],
                'timeout' => 4.0,
            ]);
        } catch (TransportExceptionInterface) {
            return CompanyLookupResult::unavailable();
        }

        if ($response->getStatusCode() >= 400) {
            return CompanyLookupResult::unavailable();
        }

        try {
            $payload = $response->toArray(false);
        } catch (DecodingExceptionInterface) {
            return CompanyLookupResult::unavailable();
        }
        $results = $payload['results'] ?? [];
        if (!is_array($results) || [] === $results) {
            return CompanyLookupResult::notFound();
        }

        $result = $this->selectMatchingResult($results, $normalized);
        if (!is_array($result)) {
            return CompanyLookupResult::notFound();
        }

        return CompanyLookupResult::ok($this->normalizeResult($result, $normalized));
    }

    /**
     * @param array<int, mixed> $results
     *
     * @return array<string, mixed>|null
     */
    private function selectMatchingResult(array $results, string $siret): ?array
    {
        foreach ($results as $result) {
            if (!is_array($result)) {
                continue;
            }

            $siege = $result['siege'] ?? null;
            if (is_array($siege) && ($siege['siret'] ?? null) === $siret) {
                return $result;
            }

            $matches = $result['matching_etablissements'] ?? [];
            if (is_array($matches)) {
                foreach ($matches as $match) {
                    if (is_array($match) && ($match['siret'] ?? null) === $siret) {
                        return $result;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>
     */
    private function normalizeResult(array $result, string $siret): array
    {
        $siege = is_array($result['siege'] ?? null) ? $result['siege'] : [];
        $addressSource = $siege;
        if (($siege['siret'] ?? null) !== $siret) {
            $addressSource = $this->matchEtablissement($result, $siret) ?? $siege;
        }

        $legalName = $result['nom_raison_sociale'] ?? $result['nom_complet'] ?? null;
        $displayName = $this->resolveDisplayName($result, $siege) ?? $legalName;

        return [
            'siret' => $siret,
            'siren' => $result['siren'] ?? null,
            'legalName' => $legalName,
            'displayName' => $displayName,
            'legalNature' => $result['nature_juridique'] ?? null,
            'organizationType' => $this->resolveOrganizationType($result),
            'address' => $this->normalizeAddress($addressSource),
        ];
    }

    /**
     * @param array<string, mixed> $result
     *
     * @return array<string, mixed>|null
     */
    private function matchEtablissement(array $result, string $siret): ?array
    {
        $matches = $result['matching_etablissements'] ?? [];
        if (!is_array($matches)) {
            return null;
        }

        foreach ($matches as $match) {
            if (is_array($match) && ($match['siret'] ?? null) === $siret) {
                return $match;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $result
     * @param array<string, mixed> $siege
     */
    private function resolveDisplayName(array $result, array $siege): ?string
    {
        if (!empty($result['sigle'])) {
            return $result['sigle'];
        }

        if (!empty($siege['nom_commercial'])) {
            return $siege['nom_commercial'];
        }

        $enseignes = $siege['liste_enseignes'] ?? null;
        if (is_array($enseignes) && !empty($enseignes[0])) {
            return $enseignes[0];
        }

        return $result['nom_complet'] ?? $result['nom_raison_sociale'] ?? null;
    }

    /**
     * @param array<string, mixed> $result
     */
    private function resolveOrganizationType(array $result): ?string
    {
        $complements = $result['complements'] ?? [];
        if (!is_array($complements)) {
            $complements = [];
        }

        if (($complements['est_association'] ?? false) === true) {
            return 'ASSOCIATION';
        }

        if (($complements['collectivite_territoriale'] ?? null) || ($complements['est_service_public'] ?? false) === true) {
            return 'COLLECTIVITE';
        }

        if (($complements['est_entrepreneur_individuel'] ?? false) === true) {
            return 'ENTREPRISE';
        }

        if (!empty($result['categorie_entreprise'])) {
            return 'ENTREPRISE';
        }

        return null;
    }

    /**
     * @param array<string, mixed> $address
     *
     * @return array<string, mixed>
     */
    private function normalizeAddress(array $address): array
    {
        $parts = array_filter([
            $address['numero_voie'] ?? null,
            $address['indice_repetition'] ?? null,
            $address['type_voie'] ?? null,
            $address['libelle_voie'] ?? null,
        ]);

        $line1 = trim(implode(' ', $parts));
        if ('' === $line1 && !empty($address['geo_adresse'])) {
            $line1 = $address['geo_adresse'];
        }
        if ('' === $line1 && !empty($address['adresse'])) {
            $line1 = $address['adresse'];
        }

        $country = $address['code_pays_etranger'] ?? null;
        if (empty($country)) {
            $country = 'FR';
        }

        return [
            'line1' => $line1 ?: null,
            'line2' => $address['complement_adresse'] ?? null,
            'line3' => $address['distribution_speciale'] ?? null,
            'postalCode' => $address['code_postal'] ?? null,
            'city' => $address['libelle_commune'] ?? ($address['libelle_commune_etranger'] ?? null),
            'country' => $country,
        ];
    }

    private function passesLuhn(string $value): bool
    {
        $sum = 0;
        $length = strlen($value);

        for ($i = $length - 1; $i >= 0; --$i) {
            $digit = (int) $value[$i];
            if (($i % 2) ^ ($length % 2)) {
                $sum += $digit;
            } else {
                $sum += (int) (2 * $digit / 10) + (2 * $digit) % 10;
            }
        }

        return $sum > 0 && 0 === $sum % 10;
    }
}
