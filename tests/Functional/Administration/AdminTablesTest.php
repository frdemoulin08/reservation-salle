<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class AdminTablesTest extends DatabaseWebTestCase
{
    public function testSitesIndexRenders(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/sites');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Sites');
        self::assertSelectorExists('table');
    }

    public function testUsersIndexRenders(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/utilisateurs');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Utilisateurs');
        self::assertSelectorExists('table');
    }

    public function testAuthenticationLogsIndexRenders(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/authentication-logs');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Journaux d’authentification');
        self::assertSelectorExists('table');
    }
}
