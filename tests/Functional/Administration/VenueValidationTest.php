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
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/sites/nouveau', [
            'venue' => [
                'name' => '',
                'description' => '',
                'addressLine1' => '',
                'addressPostalCode' => '',
                'addressCountry' => '',
                'addressCity' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#venue_name-error', 'Le nom du site est obligatoire');
        self::assertSelectorTextContains('#venue_description-error', 'Le texte descriptif est obligatoire');
        self::assertSelectorTextContains('#venue_addressLine1-error', 'L’adresse du site est obligatoire');
        self::assertSelectorTextContains('#venue_addressPostalCode-error', 'Le code postal est obligatoire');
        self::assertSelectorTextContains('#venue_addressCountry-error', 'Le pays est obligatoire');
        self::assertSelectorTextContains('#venue_addressCity-error', 'La commune est obligatoire');
    }

    public function testCreateVenueRejectsDescriptionTooLong(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/sites/nouveau', [
            'venue' => [
                'name' => 'Site Test',
                'description' => str_repeat('a', 501),
                'addressLine1' => '1 rue des Tests',
                'addressPostalCode' => '08000',
                'addressCountry' => 'FR',
                'addressCity' => 'Charleville',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#venue_description-error', 'Le texte descriptif ne peut pas dépasser 500 caractères');
    }

    public function testEditVenueRejectsDescriptionTooLong(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $repository = self::getContainer()->get(\App\Repository\VenueRepository::class);
        $venue = $repository->findOneBy([]);
        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/sites/'.$venue->getPublicIdentifier().'/modifier', [
            'venue' => [
                'name' => 'Site Test',
                'description' => str_repeat('b', 501),
                'addressLine1' => '1 rue des Tests',
                'addressPostalCode' => '08000',
                'addressCountry' => 'FR',
                'addressCity' => 'Charleville',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#venue_description-error', 'Le texte descriptif ne peut pas dépasser 500 caractères');
    }
}
