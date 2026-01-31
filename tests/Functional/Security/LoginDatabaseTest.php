<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\DatabaseWebTestCase;

class LoginDatabaseTest extends DatabaseWebTestCase
{
    private function getCsrfToken(): string
    {
        $tokenManager = self::getContainer()->get('security.csrf.token_manager');

        return $tokenManager->getToken('authenticate')->getValue();
    }

    public function testLoginSucceedsWithValidCredentials(): void
    {
        $client = $this->client;
        self::assertNotNull($client);

        $client->request('GET', '/connexion');

        $client->request('POST', '/connexion', [
            'email' => 'frederic.demoulin@cd08.fr',
            'password' => 'Abcdef123456@',
            '_csrf_token' => $this->getCsrfToken(),
        ]);

        self::assertResponseRedirects('/administration');
        $client->followRedirect();
        self::assertResponseIsSuccessful();
    }
}
