<?php

namespace App\Tests\Functional\Security;

use App\Repository\ResetPasswordLogRepository;
use App\Repository\UserRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordTest extends DatabaseWebTestCase
{
    public function testRequestFormRenders(): void
    {
        $client = $this->client;
        $client->request('GET', '/mot-de-passe-oublie');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mot de passe oublié');
    }

    public function testRequestWithKnownEmailRedirectsToCheckEmail(): void
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/mot-de-passe-oublie');

        $form = $crawler->selectButton('Envoyer le lien')->form([
            'reset_password_request_form[email]' => 'frederic.demoulin@cd08.fr',
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/mot-de-passe-oublie/envoye');

        $client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Vérifiez votre email');
    }

    public function testRequestCreatesLogEntry(): void
    {
        $client = $this->client;
        $crawler = $client->request('GET', '/mot-de-passe-oublie');

        $logRepository = self::getContainer()->get(ResetPasswordLogRepository::class);
        $beforeCount = $logRepository->count(['eventType' => 'RESET_REQUEST']);

        $form = $crawler->selectButton('Envoyer le lien')->form([
            'reset_password_request_form[email]' => 'frederic.demoulin@cd08.fr',
        ]);
        $client->submit($form);

        $afterCount = $logRepository->count(['eventType' => 'RESET_REQUEST']);
        $this->assertSame($beforeCount + 1, $afterCount);
    }

    public function testResetWithInvalidTokenRedirects(): void
    {
        $client = $this->client;
        $client->request('GET', '/reinitialiser-mot-de-passe/invalide');

        $this->assertResponseRedirects('/mot-de-passe-oublie');
    }

    public function testResetWithValidTokenUpdatesPassword(): void
    {
        $client = $this->client;
        $container = self::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);
        $this->assertNotNull($user);

        $resetHelper = $container->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetHelper->generateResetToken($user);

        $client->request('GET', '/reinitialiser-mot-de-passe/'.$resetToken->getToken());
        $this->assertResponseIsSuccessful();

        $newPassword = 'Resetpass1234!';
        $form = $client->getCrawler()->selectButton('Mettre à jour le mot de passe')->form([
            'change_password_form[plainPassword]' => $newPassword,
        ]);

        $client->submit($form);
        $this->assertResponseRedirects('/connexion');

        $reloadedUser = $userRepository->find($user->getId());
        $this->assertNotNull($reloadedUser);

        $passwordHasher = $container->get(UserPasswordHasherInterface::class);
        $this->assertTrue($passwordHasher->isPasswordValid($reloadedUser, $newPassword));
    }

    public function testResetInvalidatesExistingSession(): void
    {
        $client = $this->loginAsAdmin();
        $container = self::getContainer();

        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);
        $this->assertNotNull($user);

        $resetHelper = $container->get(ResetPasswordHelperInterface::class);
        $resetToken = $resetHelper->generateResetToken($user);

        $client->request('GET', '/reinitialiser-mot-de-passe/'.$resetToken->getToken());
        $form = $client->getCrawler()->selectButton('Mettre à jour le mot de passe')->form([
            'change_password_form[plainPassword]' => 'Resetpass1234!',
        ]);
        $client->submit($form);

        $client->request('GET', '/administration');
        $this->assertResponseRedirects('/connexion');
    }

    public function testRequestRevokesPreviousTokens(): void
    {
        $client = $this->client;
        $container = self::getContainer();

        $crawler = $client->request('GET', '/mot-de-passe-oublie');
        $form = $crawler->selectButton('Envoyer le lien')->form([
            'reset_password_request_form[email]' => 'frederic.demoulin@cd08.fr',
        ]);
        $client->submit($form);

        $crawler = $client->request('GET', '/mot-de-passe-oublie');
        $form = $crawler->selectButton('Envoyer le lien')->form([
            'reset_password_request_form[email]' => 'frederic.demoulin@cd08.fr',
        ]);
        $client->submit($form);

        $userRepository = $container->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);

        $resetRequestRepository = $container->get('doctrine')->getRepository(\App\Entity\ResetPasswordRequest::class);
        $count = $resetRequestRepository->count(['user' => $user]);
        $this->assertSame(1, $count);
    }
}
