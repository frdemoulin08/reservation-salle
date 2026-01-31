<?php

namespace App\Tests\Functional\Administration;

use App\Repository\SiteRepository;
use App\Tests\Functional\DatabaseWebTestCase;

class SiteCrudTest extends DatabaseWebTestCase
{
    public function testCreateSiteSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/sites/new');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le site')->form();
        $form['site[name]'] = 'Site Test';
        $form['site[city]'] = 'Charleville';
        $form['site[status]'] = 'Ouvert';
        $form['site[address]'] = '1 rue des Tests';
        $form['site[capacity]'] = '120';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();

        self::assertResponseIsSuccessful();
        $repository = self::getContainer()->get(SiteRepository::class);
        self::assertNotNull($repository->findOneBy(['name' => 'Site Test']));
    }

    public function testEditSiteSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(SiteRepository::class);
        $site = $repository->findOneBy([]);

        self::assertNotNull($site, 'Aucun site disponible pour le test.');

        $crawler = $client->request('GET', '/administration/sites/'.$site->getId().'/edit');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['site[name]'] = 'Site Edit Test';
        $form['site[city]'] = 'Sedan';
        $form['site[status]'] = 'Maintenance';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($site->getId());
        self::assertSame('Site Edit Test', $updated?->getName());
        self::assertSame('Sedan', $updated?->getCity());
        self::assertSame('Maintenance', $updated?->getStatus());
    }

    public function testDeleteSiteSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(SiteRepository::class);
        $site = $repository->findOneBy([]);

        self::assertNotNull($site, 'Aucun site disponible pour le test.');

        $client->request('GET', '/administration/sites');
        $tokenManager = $client->getContainer()->get('security.csrf.token_manager');
        $token = $tokenManager->getToken('delete_site')->getValue();

        $before = $repository->count([]);
        $client->request('POST', '/administration/sites/'.$site->getId().'/delete', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/sites');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }
}
