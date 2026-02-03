<?php

namespace App\Tests\Unit\Service;

use App\Service\BanAddressClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class BanAddressClientTest extends TestCase
{
    public function testSearchMapsBanResults(): void
    {
        $payload = [
            'features' => [
                [
                    'properties' => [
                        'label' => '10 Rue de la Paix 08000 Charleville-Mézières',
                        'name' => 'Rue de la Paix',
                        'housenumber' => '10',
                        'street' => 'Rue de la Paix',
                        'postcode' => '08000',
                        'city' => 'Charleville-Mézières',
                        'context' => '08, Ardennes, Grand Est',
                        'id' => '08000_1234',
                        'type' => 'housenumber',
                        'score' => 0.98,
                    ],
                    'geometry' => [
                        'coordinates' => [4.7167, 49.7667],
                    ],
                ],
            ],
        ];

        $client = new MockHttpClient([
            new MockResponse(json_encode($payload, JSON_THROW_ON_ERROR)),
        ]);
        $service = new BanAddressClient($client, 'https://api-adresse.data.gouv.fr');

        $results = $service->search('10 rue', 5);

        self::assertCount(1, $results);
        self::assertSame('10 Rue de la Paix 08000 Charleville-Mézières', $results[0]['label']);
        self::assertSame('10 Rue de la Paix', $results[0]['line1']);
        self::assertSame('08000', $results[0]['postcode']);
        self::assertSame('Charleville-Mézières', $results[0]['city']);
        self::assertSame('08000_1234', $results[0]['id']);
        self::assertSame(49.7667, $results[0]['latitude']);
        self::assertSame(4.7167, $results[0]['longitude']);
    }
}
