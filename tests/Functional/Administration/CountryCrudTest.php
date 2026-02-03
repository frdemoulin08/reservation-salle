<?php

namespace App\Tests\Functional\Administration;

use App\Repository\CountryRepository;
use App\Tests\Functional\DatabaseWebTestCase;

class CountryCrudTest extends DatabaseWebTestCase
{
    public function testCreateCountrySucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/pays/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le pays')->form();
        $form['country[label]'] = 'Testland';
        $form['country[code]'] = 'TL';
        $form['country[dialingCode]'] = '+99';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(CountryRepository::class);
        self::assertNotNull($repository->findOneBy(['code' => 'TL']));
    }

    public function testEditCountrySucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(CountryRepository::class);
        $country = $repository->findOneBy([]);

        self::assertNotNull($country, 'Aucun pays disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/pays/'.$country->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['country[label]'] = 'Pays Test';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($country->getId());
        self::assertSame('Pays Test', $updated?->getLabel());
    }

    public function testDeleteCountrySucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(CountryRepository::class);
        $country = $repository->findOneBy([]);

        self::assertNotNull($country, 'Aucun pays disponible pour le test.');

        $client->request('GET', '/administration/parametrage/pays');

        $before = $repository->count([]);
        $requestStack = $client->getContainer()->get('request_stack');
        $sessionFactory = $client->getContainer()->get('session.factory');
        $session = $sessionFactory->createSession();
        $sessionName = $session->getName();
        $sessionCookie = $client->getCookieJar()->get($sessionName);
        if (null !== $sessionCookie) {
            $session->setId($sessionCookie->getValue());
        }
        $session->start();

        $request = \Symfony\Component\HttpFoundation\Request::create('/');
        $request->setSession($session);
        $requestStack->push($request);

        $tokenManager = $client->getContainer()->get('security.csrf.token_manager');
        $token = $tokenManager->getToken('delete_country')->getValue();

        $client->request('POST', '/administration/parametrage/pays/'.$country->getPublicIdentifier().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/pays');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }
}
