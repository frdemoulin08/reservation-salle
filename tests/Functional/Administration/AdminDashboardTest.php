<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class AdminDashboardTest extends DatabaseWebTestCase
{
    public function testAdminDashboardAccessibleForAdmin(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Tableau de bord');
    }
}
