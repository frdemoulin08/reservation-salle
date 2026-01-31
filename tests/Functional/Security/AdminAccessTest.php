<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminAccessTest extends WebTestCase
{
    public function testAdminRequiresAuthentication(): void
    {
        $client = self::createClient();

        $client->request('GET', '/administration');

        self::assertResponseRedirects('/connexion');
    }
}
