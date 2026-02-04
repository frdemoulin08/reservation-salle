<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class EventTypeValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="event_type[_token]"]')->attr('value');
    }

    public function testCreateEventTypeRequiresLabelAndCode(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-evenement/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/types-evenement/nouveau', [
            'event_type' => [
                'label' => '',
                'code' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#event_type_label-error', 'Le libellé est obligatoire.');
        self::assertSelectorTextContains('#event_type_code-error', 'Le code est obligatoire.');
    }

    public function testCreateEventTypeRejectsInvalidCodeFormat(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-evenement/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/types-evenement/nouveau', [
            'event_type' => [
                'label' => 'Événement test',
                'code' => 'test-event',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#event_type_code-error', 'Le code doit être en anglais et au format UPPER_SNAKE_CASE.');
    }
}
