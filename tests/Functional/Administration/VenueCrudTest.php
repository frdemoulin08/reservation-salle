<?php

namespace App\Tests\Functional\Administration;

use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;

class VenueCrudTest extends DatabaseWebTestCase
{
    public function testCreateVenueSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le site')->form();
        $form['venue[name]'] = 'Site Test';
        $form['venue[addressLine1]'] = '1 rue des Tests';
        $form['venue[addressPostalCode]'] = '08000';
        $form['venue[addressCity]'] = 'Charleville';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        $repository = self::getContainer()->get(VenueRepository::class);
        self::assertNotNull($repository->findOneBy(['name' => 'Site Test']));
    }

    public function testEditVenueSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(VenueRepository::class);
        $venue = $repository->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['venue[name]'] = 'Site Edit Test';
        $form['venue[addressCity]'] = 'Sedan';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($venue->getId());
        self::assertSame('Site Edit Test', $updated?->getName());
        self::assertSame('Sedan', $updated?->getAddress()?->getCity());
    }

    public function testDeleteVenueSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(VenueRepository::class);
        $venue = $repository->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $client->request('GET', '/administration/sites');

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
        $token = $tokenManager->getToken('delete_venue')->getValue();

        $client->request('POST', '/administration/sites/'.$venue->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/sites');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }
}
