<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class TableAjaxTest extends DatabaseWebTestCase
{
    public function testSitesAjaxReturnsTableFragment(): void
    {
        $client = $this->loginAsAdmin();

        $client->xmlHttpRequest('GET', '/administration/sites', [
            'page' => 1,
            'sort' => 'name',
            'direction' => 'asc',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('table');
        self::assertStringNotContainsString('<html', $client->getResponse()->getContent() ?? '');
    }
}
