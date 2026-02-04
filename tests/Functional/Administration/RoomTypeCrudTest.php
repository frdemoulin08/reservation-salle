<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Room;
use App\Entity\RoomType;
use App\Repository\RoomTypeRepository;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class RoomTypeCrudTest extends DatabaseWebTestCase
{
    public function testIndexListsRoomTypes(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/parametrage/types-salle');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Salle de réunion');
    }

    public function testCreateRoomTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-salle/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le type')->form();
        $form['room_type[label]'] = 'Type de test';
        $form['room_type[code]'] = 'TEST_ROOM';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(RoomTypeRepository::class);
        self::assertNotNull($repository->findOneBy(['code' => 'TEST_ROOM']));
    }

    public function testEditRoomTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(RoomTypeRepository::class);
        $roomType = $repository->findOneBy([]);

        self::assertNotNull($roomType, 'Aucun type de salle disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/types-salle/'.$roomType->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['room_type[label]'] = 'Type mis à jour';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($roomType->getId());
        self::assertSame('Type mis à jour', $updated?->getLabel());
    }

    public function testEditRoomTypeDoesNotChangeCode(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(RoomTypeRepository::class);
        $roomType = $repository->findOneBy([]);

        self::assertNotNull($roomType, 'Aucun type de salle disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/types-salle/'.$roomType->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $originalCode = $roomType->getCode();
        $token = (string) $crawler->filter('input[name="room_type[_token]"]')->attr('value');

        $client->request('POST', '/administration/parametrage/types-salle/'.$roomType->getId().'/modifier', [
            'room_type' => [
                'label' => 'Type modifié',
                'code' => 'SHOULD_NOT_CHANGE',
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();

        $updated = $repository->find($roomType->getId());
        self::assertSame($originalCode, $updated?->getCode());
    }

    public function testDeleteRoomTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(RoomTypeRepository::class);
        $roomType = $repository->findOneBy([]);

        self::assertNotNull($roomType, 'Aucun type de salle disponible pour le test.');

        $before = $repository->count([]);
        $token = $this->getDeleteToken($client);

        $client->request('POST', '/administration/parametrage/types-salle/'.$roomType->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/types-salle');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }

    public function testDeleteRoomTypeIsRejectedWhenUsed(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $venue = self::getContainer()->get(VenueRepository::class)->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $roomType = (new RoomType())
            ->setCode('used-type')
            ->setLabel('Type utilisé');
        $room = (new Room())
            ->setVenue($venue)
            ->setName('Salle test');
        $room->addRoomType($roomType);

        $entityManager->persist($roomType);
        $entityManager->persist($room);
        $entityManager->flush();

        $repository = $entityManager->getRepository(RoomType::class);
        $before = $repository->count([]);

        $token = $this->getDeleteToken($client);
        $client->request('POST', '/administration/parametrage/types-salle/'.$roomType->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/types-salle');

        $entityManager->clear();
        $after = $repository->count([]);
        self::assertSame($before, $after);
    }

    private function getDeleteToken($client): string
    {
        $client->request('GET', '/administration/parametrage/types-salle');

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

        return $tokenManager->getToken('delete_room_type')->getValue();
    }
}
