<?php

namespace App\Tests\Functional\Administration;

use App\Repository\UserRepository;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;

class VenueReferentTest extends DatabaseWebTestCase
{
    public function testReferentSelectFiltersRoles(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $options = $crawler
            ->filter('#venue_referenceContactUser option')
            ->each(static fn ($option) => trim($option->text()));

        self::assertContains('Marion Leblanc (metier.admin@cd08.fr)', $options);
        self::assertContains('Luc Garnier (gestion.admin@cd08.fr)', $options);
        self::assertContains('Sophie Morel (gestion.metier@cd08.fr)', $options);

        self::assertNotContains('Frederic Demoulin (frederic.demoulin@cd08.fr)', $options);
        self::assertNotContains('Clément JACQUET (clement.jacquet@cd08.fr)', $options);
    }

    public function testCreateVenueWithReferentPersists(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $venueRepository = self::getContainer()->get(VenueRepository::class);

        $referent = $userRepository->findOneBy(['email' => 'metier.admin@cd08.fr']);
        self::assertNotNull($referent, 'Référent introuvable pour le test.');

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le site')->form();
        $form['venue[name]'] = 'Site Référent Test';
        $form['venue[description]'] = 'Site de test avec référent.';
        $form['venue[referenceContactUser]'] = (string) $referent->getId();
        $form['venue[addressLine1]'] = '1 rue des Tests';
        $form['venue[addressPostalCode]'] = '08000';
        $form['venue[addressCity]'] = 'Charleville';
        $form['venue[addressCountry]'] = 'FR';

        $client->submit($form);

        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();

        $created = $venueRepository->findOneBy(['name' => 'Site Référent Test']);
        self::assertNotNull($created);
        self::assertSame($referent->getId(), $created->getReferenceContactUser()?->getId());

        $referentBlock = $crawler->filterXPath('//dt[contains(normalize-space(.), "Référent SPSL")]/following-sibling::dd[1]');
        self::assertSame(1, $referentBlock->count());
        self::assertStringContainsString('Marion Leblanc', $referentBlock->text());
        self::assertStringContainsString('metier.admin@cd08.fr', $referentBlock->text());
    }

    public function testCreateVenueWithoutReferentShowsPlaceholder(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $venueRepository = self::getContainer()->get(VenueRepository::class);

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le site')->form();
        $form['venue[name]'] = 'Site Sans Référent';
        $form['venue[description]'] = 'Site de test sans référent.';
        $form['venue[addressLine1]'] = '1 rue des Tests';
        $form['venue[addressPostalCode]'] = '08000';
        $form['venue[addressCity]'] = 'Charleville';
        $form['venue[addressCountry]'] = 'FR';

        $client->submit($form);

        self::assertResponseRedirects();
        $crawler = $client->followRedirect();
        self::assertResponseIsSuccessful();

        $created = $venueRepository->findOneBy(['name' => 'Site Sans Référent']);
        self::assertNotNull($created);
        self::assertNull($created->getReferenceContactUser());

        $referentBlock = $crawler->filterXPath('//dt[contains(normalize-space(.), "Référent SPSL")]/following-sibling::dd[1]');
        self::assertSame(1, $referentBlock->count());
        self::assertSame('—', trim($referentBlock->text()));
    }

    public function testEditVenueUpdatesReferent(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $venueRepository = self::getContainer()->get(VenueRepository::class);

        $referent = $userRepository->findOneBy(['email' => 'gestion.admin@cd08.fr']);
        self::assertNotNull($referent, 'Référent introuvable pour le test.');

        $venue = $venueRepository->findOneBy([]);
        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['venue[name]'] = $venue->getName() ?: 'Site Test';
        $form['venue[description]'] = $venue->getDescription() ?: 'Description test.';
        $form['venue[referenceContactUser]'] = (string) $referent->getId();
        $form['venue[addressLine1]'] = $venue->getAddress()?->getLine1() ?: '1 rue des Tests';
        $form['venue[addressPostalCode]'] = $venue->getAddress()?->getPostalCode() ?: '08000';
        $form['venue[addressCity]'] = $venue->getAddress()?->getCity() ?: 'Charleville';
        $form['venue[addressCountry]'] = $venue->getAddress()?->getCountry() ?: 'FR';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $venueRepository->find($venue->getId());
        self::assertSame($referent->getId(), $updated?->getReferenceContactUser()?->getId());
    }

    public function testEditVenueRemovesReferent(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $userRepository = self::getContainer()->get(UserRepository::class);
        $venueRepository = self::getContainer()->get(VenueRepository::class);

        $referent = $userRepository->findOneBy(['email' => 'gestion.admin@cd08.fr']);
        self::assertNotNull($referent, 'Référent introuvable pour le test.');

        $venue = $venueRepository->findOneBy([]);
        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $venue->setReferenceContactUser($referent);
        $entityManager = self::getContainer()->get(\Doctrine\ORM\EntityManagerInterface::class);
        $entityManager->flush();

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['venue[name]'] = $venue->getName() ?: 'Site Test';
        $form['venue[description]'] = $venue->getDescription() ?: 'Description test.';
        $form['venue[referenceContactUser]'] = '';
        $form['venue[addressLine1]'] = $venue->getAddress()?->getLine1() ?: '1 rue des Tests';
        $form['venue[addressPostalCode]'] = $venue->getAddress()?->getPostalCode() ?: '08000';
        $form['venue[addressCity]'] = $venue->getAddress()?->getCity() ?: 'Charleville';
        $form['venue[addressCountry]'] = $venue->getAddress()?->getCountry() ?: 'FR';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $venueRepository->find($venue->getId());
        self::assertNull($updated?->getReferenceContactUser());

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $referentBlock = $crawler->filterXPath('//dt[contains(normalize-space(.), "Référent SPSL")]/following-sibling::dd[1]');
        self::assertSame(1, $referentBlock->count());
        self::assertSame('—', trim($referentBlock->text()));
    }
}
