<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class CronTasksIndexTest extends DatabaseWebTestCase
{
    public function testCronTasksIndexRenders(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/journal-taches');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Journaux des tâches planifiées');
        self::assertSelectorExists('table');
    }
}
