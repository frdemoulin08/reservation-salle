<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class SiteValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="site[_token]"]')->attr('value');
    }

    public function testCreateSiteRequiresNameAndCity(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/sites/new');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/sites/new', [
            'site' => [
                'name' => '',
                'city' => '',
                'status' => 'Ouvert',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#site_name-error', 'Le nom du site est obligatoire');
        self::assertSelectorTextContains('#site_city-error', 'La commune est obligatoire');
    }
}
