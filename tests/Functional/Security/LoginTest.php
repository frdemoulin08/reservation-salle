<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginTest extends WebTestCase
{
    private function getCsrfToken(): string
    {
        $tokenManager = self::getContainer()->get('security.csrf.token_manager');

        return $tokenManager->getToken('authenticate')->getValue();
    }

    public function testLoginPageRenders(): void
    {
        $client = self::createClient();

        $client->request('GET', '/connexion');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Connexion à votre compte');
        self::assertSelectorExists('form[action="/connexion"]');
        self::assertSelectorExists('input[name="email"]');
        self::assertSelectorExists('input[name="password"]');
    }

    public function testLoginRequiresEmail(): void
    {
        $client = self::createClient();

        $client->request('POST', '/connexion', [
            'email' => '',
            'password' => 'test',
            '_csrf_token' => $this->getCsrfToken(),
        ]);

        self::assertResponseRedirects('/connexion');
        $client->followRedirect();

        self::assertSelectorTextContains('#email-error', 'L’adresse email est obligatoire');
    }

    public function testLoginRejectsInvalidEmail(): void
    {
        $client = self::createClient();

        $client->request('POST', '/connexion', [
            'email' => 'invalid-email',
            'password' => 'test',
            '_csrf_token' => $this->getCsrfToken(),
        ]);

        self::assertResponseRedirects('/connexion');
        $client->followRedirect();

        self::assertSelectorTextContains('#email-error', 'Le format de l’email est invalide');
    }

    public function testLoginRequiresPassword(): void
    {
        $client = self::createClient();

        $client->request('POST', '/connexion', [
            'email' => 'frederic.demoulin@cd08.fr',
            'password' => '',
            '_csrf_token' => $this->getCsrfToken(),
        ]);

        self::assertResponseRedirects('/connexion');
        $client->followRedirect();

        self::assertSelectorTextContains('#password-error', 'Le mot de passe est obligatoire');
    }
}
