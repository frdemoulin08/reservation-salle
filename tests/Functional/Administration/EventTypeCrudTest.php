<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Embeddable\Address;
use App\Entity\EventType;
use App\Entity\Organization;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Repository\EventTypeRepository;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EventTypeCrudTest extends DatabaseWebTestCase
{
    public function testIndexListsEventTypes(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/parametrage/types-evenement');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Conférence');
    }

    public function testCreateEventTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-evenement/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le type')->form();
        $form['event_type[label]'] = 'Événement test';
        $form['event_type[code]'] = 'TEST_EVENT';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(EventTypeRepository::class);
        self::assertNotNull($repository->findOneBy(['code' => 'TEST_EVENT']));
    }

    public function testEditEventTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(EventTypeRepository::class);
        $eventType = $repository->findOneBy([]);

        self::assertNotNull($eventType, 'Aucun type d’événement disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/types-evenement/'.$eventType->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['event_type[label]'] = 'Type mis à jour';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($eventType->getId());
        self::assertSame('Type mis à jour', $updated?->getLabel());
    }

    public function testEditEventTypeDoesNotChangeCode(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(EventTypeRepository::class);
        $eventType = $repository->findOneBy([]);

        self::assertNotNull($eventType, 'Aucun type d’événement disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/types-evenement/'.$eventType->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $originalCode = $eventType->getCode();
        $token = (string) $crawler->filter('input[name="event_type[_token]"]')->attr('value');

        $client->request('POST', '/administration/parametrage/types-evenement/'.$eventType->getId().'/modifier', [
            'event_type' => [
                'label' => 'Type modifié',
                'code' => 'SHOULD_NOT_CHANGE',
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();

        $updated = $repository->find($eventType->getId());
        self::assertSame($originalCode, $updated?->getCode());
    }

    public function testDeleteEventTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(EventTypeRepository::class);
        $eventType = $repository->findOneBy([]);

        self::assertNotNull($eventType, 'Aucun type d’événement disponible pour le test.');

        $before = $repository->count([]);
        $token = $this->getDeleteToken($client);

        $client->request('POST', '/administration/parametrage/types-evenement/'.$eventType->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/types-evenement');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }

    public function testDeleteEventTypeIsRejectedWhenUsed(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $venue = self::getContainer()->get(VenueRepository::class)->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $eventType = (new EventType())
            ->setCode('USED_EVENT')
            ->setLabel('Type utilisé');

        $room = (new Room())
            ->setVenue($venue)
            ->setName('Salle test');

        $organization = $this->createOrganization();

        $reservation = (new Reservation())
            ->setRoom($room)
            ->setOrganization($organization)
            ->setEventType($eventType)
            ->setStartDate(new \DateTimeImmutable('2026-03-10 09:00:00'))
            ->setEndDate(new \DateTimeImmutable('2026-03-10 10:00:00'))
            ->setStatus('CONFIRMED')
            ->setTicketingType('NONE');

        $entityManager->persist($eventType);
        $entityManager->persist($room);
        $entityManager->persist($organization);
        $entityManager->persist($reservation);
        $entityManager->flush();

        $repository = $entityManager->getRepository(EventType::class);
        $before = $repository->count([]);

        $token = $this->getDeleteToken($client);
        $client->request('POST', '/administration/parametrage/types-evenement/'.$eventType->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/types-evenement');

        $entityManager->clear();
        $after = $repository->count([]);
        self::assertSame($before, $after);
    }

    private function createOrganization(): Organization
    {
        $headOffice = (new Address())
            ->setLine1('1 rue du Test')
            ->setPostalCode('08000')
            ->setCity('Charleville')
            ->setCountry('FR');

        $billing = (new Address())
            ->setLine1('1 rue du Test')
            ->setPostalCode('08000')
            ->setCity('Charleville')
            ->setCountry('FR');

        return (new Organization())
            ->setLegalName('Association test')
            ->setDisplayName('Association test')
            ->setHeadOfficeAddress($headOffice)
            ->setBillingAddress($billing)
            ->setBillingSameAsHeadOffice(true);
    }

    private function getDeleteToken($client): string
    {
        $client->request('GET', '/administration/parametrage/types-evenement');

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

        return $tokenManager->getToken('delete_event_type')->getValue();
    }
}
