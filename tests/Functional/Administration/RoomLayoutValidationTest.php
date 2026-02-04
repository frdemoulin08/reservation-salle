<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class RoomLayoutValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="room_layout[_token]"]')->attr('value');
    }

    public function testCreateRoomLayoutRequiresLabelAndCode(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/configurations-salle/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/configurations-salle/nouveau', [
            'room_layout' => [
                'label' => '',
                'code' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#room_layout_label-error', 'Le libellé est obligatoire.');
        self::assertSelectorTextContains('#room_layout_code-error', 'Le code est obligatoire.');
    }

    public function testCreateRoomLayoutRejectsInvalidCodeFormat(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/configurations-salle/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/configurations-salle/nouveau', [
            'room_layout' => [
                'label' => 'Disposition test',
                'code' => 'test-layout',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#room_layout_code-error', 'Le code doit être en anglais et au format UPPER_SNAKE_CASE.');
    }
}
