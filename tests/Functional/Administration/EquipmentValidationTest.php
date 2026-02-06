<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class EquipmentValidationTest extends DatabaseWebTestCase
{
    private function getCsrfToken($crawler): string
    {
        return (string) $crawler->filter('input[name="equipment[_token]"]')->attr('value');
    }

    public function testCreateEquipmentRequiresLabelAndType(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/equipements/nouveau');
        self::assertResponseIsSuccessful();

        $client->request('POST', '/administration/parametrage/equipements/nouveau', [
            'equipment' => [
                'label' => '',
                'equipmentType' => '',
                '_token' => $this->getCsrfToken($crawler),
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('#equipment_label-error', 'Le libellé est obligatoire.');
        self::assertSelectorTextContains('#equipment_equipmentType-error', 'Le type d’équipement est obligatoire.');
    }
}
