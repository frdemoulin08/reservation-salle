<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Room;
use App\Entity\Venue;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

class BreadcrumbTest extends DatabaseWebTestCase
{
    private function assertBreadcrumbs(Crawler $crawler, array $expected): void
    {
        self::assertGreaterThan(
            0,
            $crawler->filter('nav[aria-label="Fil d’ariane"]')->count(),
            'Le fil d’ariane est introuvable.'
        );

        $labels = $crawler->filter('nav[aria-label="Fil d’ariane"] li')->each(
            fn (Crawler $node): string => $this->normalizeLabel($node->text())
        );

        self::assertSame($expected, $labels);
    }

    private function normalizeLabel(string $label): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($label));

        return $normalized ?? '';
    }

    private function createRoom(EntityManagerInterface $entityManager): Room
    {
        $venue = $entityManager->getRepository(Venue::class)->findOneBy([]);
        self::assertNotNull($venue, 'Aucun site disponible pour créer une salle.');

        $room = (new Room())
            ->setVenue($venue)
            ->setName('Salle de test');

        $entityManager->persist($room);
        $entityManager->flush();

        return $room;
    }

    public function testUsagersIndexBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/gestion/usagers');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Usagers']);
    }

    public function testUsagersNewBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/gestion/usagers/nouveau');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Usagers', 'Créer un usager']);
    }

    public function testUsagersShowBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => 'claire.dupont@asso-exemple.org']);

        self::assertNotNull($user, 'Usager de test introuvable.');

        $crawler = $client->request('GET', '/administration/gestion/usagers/'.$user->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Usagers', 'Détail de l’usager']);
    }

    public function testOrganizationsIndexBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/gestion/organisations');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Organisations']);
    }

    public function testOrganizationsNewBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/gestion/organisations/nouveau');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Organisations', 'Ajouter une organisation']);
    }

    public function testOrganizationsShowBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $repository = self::getContainer()->get(OrganizationRepository::class);
        $organization = $repository->findOneBy([]);

        self::assertNotNull($organization, 'Organisation de test introuvable.');

        $crawler = $client->request('GET', '/administration/gestion/organisations/'.$organization->getId());
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Organisations', 'Détail de l’organisation']);
    }

    public function testOrganizationsEditBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $repository = self::getContainer()->get(OrganizationRepository::class);
        $organization = $repository->findOneBy([]);

        self::assertNotNull($organization, 'Organisation de test introuvable.');

        $crawler = $client->request('GET', '/administration/gestion/organisations/'.$organization->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Organisations', 'Éditer une organisation']);
    }

    public function testVenuesIndexBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/sites');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Sites']);
    }

    public function testVenuesNewBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/sites/nouveau');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Sites', 'Ajouter un site']);
    }

    public function testVenuesShowBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $repository = self::getContainer()->get(VenueRepository::class);
        $venue = $repository->findOneBy([]);

        self::assertNotNull($venue, 'Site de test introuvable.');

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Sites', 'Détail du site']);
    }

    public function testVenuesEditBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $repository = self::getContainer()->get(VenueRepository::class);
        $venue = $repository->findOneBy([]);

        self::assertNotNull($venue, 'Site de test introuvable.');

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Gestion', 'Sites', 'Éditer un site']);
    }

    public function testRoomsIndexBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/salles');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Catalogue', 'Salles']);
    }

    public function testRoomsNewBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $crawler = $client->request('GET', '/administration/salles/nouveau');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Catalogue', 'Salles', 'Ajouter une salle']);
    }

    public function testRoomsShowBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        $crawler = $client->request('GET', '/administration/salles/'.$room->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Catalogue', 'Salles', 'Détail de la salle']);
    }

    public function testRoomsEditBreadcrumb(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        $crawler = $client->request('GET', '/administration/salles/'.$room->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Catalogue', 'Salles', 'Éditer une salle']);
    }

    public function testUsersIndexBreadcrumb(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/utilisateurs');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Administration', 'Utilisateurs']);
    }

    public function testUsersNewBreadcrumb(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/utilisateurs/nouveau');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Administration', 'Utilisateurs', 'Ajouter un utilisateur']);
    }

    public function testUsersShowBreadcrumb(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);

        self::assertNotNull($user, 'Utilisateur admin introuvable.');

        $crawler = $client->request('GET', '/administration/utilisateurs/'.$user->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Administration', 'Utilisateurs', 'Détail de l’utilisateur']);
    }

    public function testUsersEditBreadcrumb(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(UserRepository::class);
        $user = $repository->findOneBy(['email' => 'frederic.demoulin@cd08.fr']);

        self::assertNotNull($user, 'Utilisateur admin introuvable.');

        $crawler = $client->request('GET', '/administration/utilisateurs/'.$user->getPublicIdentifier().'/edition');
        self::assertResponseIsSuccessful();

        $this->assertBreadcrumbs($crawler, ['Accueil', 'Administration', 'Utilisateurs', 'Éditer un utilisateur']);
    }
}
