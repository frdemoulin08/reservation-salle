<?php

namespace App\Tests\Functional\Administration;

use App\Tests\Functional\DatabaseWebTestCase;

class ImpersonationBannerTest extends DatabaseWebTestCase
{
    public function testBannerHiddenWhenNotImpersonating(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration');

        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('div[role="alert"] a[href*="_switch_user=_exit"]');
    }

    public function testBannerVisibleWhenImpersonating(): void
    {
        $client = $this->loginAsAdmin();
        $client->followRedirects(true);

        $client->request('GET', '/administration?_switch_user=gestion.admin@cd08.fr');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('div[role="alert"]', 'Attention.');
        self::assertSelectorTextContains('div[role="alert"]', 'Quitter l’impersonation');
        self::assertSelectorTextContains('div[role="alert"]', 'Luc Garnier');
    }
}
