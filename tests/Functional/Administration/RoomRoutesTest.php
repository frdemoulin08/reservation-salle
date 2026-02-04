<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Room;
use App\Entity\Venue;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class RoomRoutesTest extends DatabaseWebTestCase
{
    public function testShowRouteUsesPublicIdentifier(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        $client->request('GET', '/administration/salles/'.$room->getPublicIdentifier());

        self::assertResponseIsSuccessful();
    }

    public function testShowRouteReturns404ForUnknownIdentifier(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $client->request('GET', '/administration/salles/'.Uuid::v4()->toRfc4122());

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
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
}
