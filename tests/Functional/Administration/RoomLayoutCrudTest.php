<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Room;
use App\Entity\RoomLayout;
use App\Repository\RoomLayoutRepository;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RoomLayoutCrudTest extends DatabaseWebTestCase
{
    public function testIndexListsRoomLayouts(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/parametrage/configurations-salle');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Théâtre');
    }

    public function testCreateRoomLayoutSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/configurations-salle/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer la configuration')->form();
        $form['room_layout[label]'] = 'Disposition test';
        $form['room_layout[code]'] = 'TEST_LAYOUT';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(RoomLayoutRepository::class);
        self::assertNotNull($repository->findOneBy(['code' => 'TEST_LAYOUT']));
    }

    public function testEditRoomLayoutSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(RoomLayoutRepository::class);
        $roomLayout = $repository->findOneBy([]);

        self::assertNotNull($roomLayout, 'Aucune configuration disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/configurations-salle/'.$roomLayout->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['room_layout[label]'] = 'Disposition mise à jour';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($roomLayout->getId());
        self::assertSame('Disposition mise à jour', $updated?->getLabel());
    }

    public function testEditRoomLayoutDoesNotChangeCode(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(RoomLayoutRepository::class);
        $roomLayout = $repository->findOneBy([]);

        self::assertNotNull($roomLayout, 'Aucune configuration disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/configurations-salle/'.$roomLayout->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $originalCode = $roomLayout->getCode();
        $token = (string) $crawler->filter('input[name="room_layout[_token]"]')->attr('value');

        $client->request('POST', '/administration/parametrage/configurations-salle/'.$roomLayout->getId().'/modifier', [
            'room_layout' => [
                'label' => 'Disposition modifiée',
                'code' => 'SHOULD_NOT_CHANGE',
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();

        $updated = $repository->find($roomLayout->getId());
        self::assertSame($originalCode, $updated?->getCode());
    }

    public function testDeleteRoomLayoutSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(RoomLayoutRepository::class);
        $roomLayout = $repository->findOneBy([]);

        self::assertNotNull($roomLayout, 'Aucune configuration disponible pour le test.');

        $before = $repository->count([]);
        $token = $this->getDeleteToken($client);

        $client->request('POST', '/administration/parametrage/configurations-salle/'.$roomLayout->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/configurations-salle');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }

    public function testDeleteRoomLayoutIsRejectedWhenUsed(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $venue = self::getContainer()->get(VenueRepository::class)->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $roomLayout = (new RoomLayout())
            ->setCode('USED_LAYOUT')
            ->setLabel('Disposition utilisée');

        $room = (new Room())
            ->setVenue($venue)
            ->setName('Salle test');
        $room->addRoomLayout($roomLayout);

        $entityManager->persist($roomLayout);
        $entityManager->persist($room);
        $entityManager->flush();

        $repository = $entityManager->getRepository(RoomLayout::class);
        $before = $repository->count([]);

        $token = $this->getDeleteToken($client);
        $client->request('POST', '/administration/parametrage/configurations-salle/'.$roomLayout->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/configurations-salle');

        $entityManager->clear();
        $after = $repository->count([]);
        self::assertSame($before, $after);
    }

    private function getDeleteToken($client): string
    {
        $client->request('GET', '/administration/parametrage/configurations-salle');

        $requestStack = $client->getContainer()->get('request_stack');
        $sessionFactory = $client->getContainer()->get('session.factory');
        $session = $sessionFactory->createSession();
        $sessionName = $session->getName();
        $sessionCookie = $client->getCookieJar()->get($sessionName);
        if (null !== $sessionCookie) {
            $session->setId($sessionCookie->getValue());
        }
        $session->start();

        $request = Request::create('/');
        $request->setSession($session);
        $requestStack->push($request);

        $tokenManager = $client->getContainer()->get('security.csrf.token_manager');

        return $tokenManager->getToken('delete_room_layout')->getValue();
    }
}
