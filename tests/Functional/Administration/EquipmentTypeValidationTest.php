<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class EquipmentTypeValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="equipment_type[_token]"]')->attr('value');
    }

    public function testCreateEquipmentTypeRequiresLabelAndCode(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-equipement/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/types-equipement/nouveau', [
            'equipment_type' => [
                'label' => '',
                'code' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#equipment_type_label-error', 'Le libellé est obligatoire.');
        self::assertSelectorTextContains('#equipment_type_code-error', 'Le code est obligatoire.');
    }

    public function testCreateEquipmentTypeRejectsInvalidCodeFormat(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-equipement/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/types-equipement/nouveau', [
            'equipment_type' => [
                'label' => 'Équipement test',
                'code' => 'test-equipment',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#equipment_type_code-error', 'Le code doit être en anglais et au format UPPER_SNAKE_CASE.');
    }
}
