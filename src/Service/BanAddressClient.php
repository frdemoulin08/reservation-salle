<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BanAddressClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, int $limit = 5): array
    {
        $query = trim($query);
        if ('' === $query) {
            return [];
        }

        $response = $this->httpClient->request('GET', rtrim($this->baseUrl, '/').'/search/', [
            'query' => [
                'q' => $query,
                'limit' => $limit,
                'autocomplete' => 1,
            ],
        ]);

        $payload = $response->toArray(false);
        $features = $payload['features'] ?? [];
        if (!is_array($features)) {
            return [];
        }

        $results = [];
        foreach ($features as $feature) {
            if (!is_array($feature)) {
                continue;
            }
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry']['coordinates'] ?? null;
            $longitude = is_array($geometry) ? ($geometry[0] ?? null) : null;
            $latitude = is_array($geometry) ? ($geometry[1] ?? null) : null;

            $line1 = trim(sprintf(
                '%s %s',
                $properties['housenumber'] ?? '',
                $properties['street'] ?? ($properties['name'] ?? '')
            ));
            $label = $properties['label'] ?? ($properties['name'] ?? $line1);
            if ('' === $line1) {
                $line1 = $label ?: $query;
            }

            $results[] = [
                'label' => $label,
                'line1' => $line1,
                'postcode' => $properties['postcode'] ?? null,
                'city' => $properties['city'] ?? null,
                'citycode' => $properties['citycode'] ?? null,
                'context' => $properties['context'] ?? null,
                'id' => $properties['id'] ?? null,
                'type' => $properties['type'] ?? null,
                'score' => $properties['score'] ?? null,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        }

        return $results;
    }
}
