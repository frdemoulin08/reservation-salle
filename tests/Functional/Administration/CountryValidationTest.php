<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class CountryValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="country[_token]"]')->attr('value');
    }

    public function testCreateCountryRequiresLabelAndCode(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/pays/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/pays/nouveau', [
            'country' => [
                'label' => '',
                'code' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#country_label-error', 'Le libellé est obligatoire.');
        self::assertSelectorTextContains('#country_code-error', 'Le code ISO est obligatoire.');
    }

    public function testCreateCountryRejectsDuplicateIsoCode(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/pays/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/pays/nouveau', [
            'country' => [
                'label' => 'France bis',
                'code' => 'FR',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#country_code-error', 'Ce code ISO est déjà utilisé.');
    }

    public function testCreateCountryRejectsInvalidIsoCodeLength(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/pays/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/pays/nouveau', [
            'country' => [
                'label' => 'Testland',
                'code' => 'FRA',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#country_code-error', 'Le code ISO doit contenir exactement 2 caractères.');
    }
}
