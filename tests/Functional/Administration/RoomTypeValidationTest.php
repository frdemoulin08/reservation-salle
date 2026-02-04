<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class RoomTypeValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="room_type[_token]"]')->attr('value');
    }

    public function testCreateRoomTypeRequiresLabelAndCode(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-salle/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/types-salle/nouveau', [
            'room_type' => [
                'label' => '',
                'code' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#room_type_label-error', 'Le libellé est obligatoire.');
        self::assertSelectorTextContains('#room_type_code-error', 'Le code est obligatoire.');
    }

    public function testCreateRoomTypeRejectsInvalidCodeFormat(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-salle/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/types-salle/nouveau', [
            'room_type' => [
                'label' => 'Salle test',
                'code' => 'test-room',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#room_type_code-error', 'Le code doit être en anglais et au format UPPER_SNAKE_CASE.');
    }
}
