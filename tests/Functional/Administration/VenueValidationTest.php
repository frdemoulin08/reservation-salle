<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class VenueValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="venue[_token]"]')->attr('value');
    }

    public function testCreateVenueRequiresNameAndCity(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/sites/nouveau', [
            'venue' => [
                'name' => '',
                'addressLine1' => '',
                'addressPostalCode' => '',
                'addressCountry' => '',
                'addressCity' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#venue_name-error', 'Le nom du site est obligatoire');
        self::assertSelectorTextContains('#venue_addressLine1-error', 'L’adresse du site est obligatoire');
        self::assertSelectorTextContains('#venue_addressPostalCode-error', 'Le code postal est obligatoire');
        self::assertSelectorTextContains('#venue_addressCountry-error', 'Le pays est obligatoire');
        self::assertSelectorTextContains('#venue_addressCity-error', 'La commune est obligatoire');
    }
}
