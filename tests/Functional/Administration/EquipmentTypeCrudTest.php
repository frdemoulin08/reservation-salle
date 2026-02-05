<?php

namespace App\Tests\Functional\Administration;

use App\Entity\EquipmentType;
use App\Entity\Room;
use App\Entity\RoomEquipment;
use App\Repository\EquipmentTypeRepository;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EquipmentTypeCrudTest extends DatabaseWebTestCase
{
    public function testIndexListsEquipmentTypes(): void
    {
        $client = $this->loginAsAdmin();

        $client->request('GET', '/administration/parametrage/types-equipement');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Technique');
    }

    public function testCreateEquipmentTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();

        $crawler = $client->request('GET', '/administration/parametrage/types-equipement/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer le type')->form();
        $form['equipment_type[label]'] = 'Équipement test';
        $form['equipment_type[code]'] = 'TEST_EQUIPMENT';

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(EquipmentTypeRepository::class);
        self::assertNotNull($repository->findOneBy(['code' => 'TEST_EQUIPMENT']));
    }

    public function testEditEquipmentTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(EquipmentTypeRepository::class);
        $equipmentType = $repository->findOneBy([]);

        self::assertNotNull($equipmentType, 'Aucun type d’équipement disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/types-equipement/'.$equipmentType->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['equipment_type[label]'] = 'Équipement mis à jour';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $updated = $repository->find($equipmentType->getId());
        self::assertSame('Équipement mis à jour', $updated?->getLabel());
    }

    public function testEditEquipmentTypeDoesNotChangeCode(): void
    {
        $client = $this->loginAsAdmin();
        $repository = self::getContainer()->get(EquipmentTypeRepository::class);
        $equipmentType = $repository->findOneBy([]);

        self::assertNotNull($equipmentType, 'Aucun type d’équipement disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/types-equipement/'.$equipmentType->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $originalCode = $equipmentType->getCode();
        $token = (string) $crawler->filter('input[name="equipment_type[_token]"]')->attr('value');

        $client->request('POST', '/administration/parametrage/types-equipement/'.$equipmentType->getId().'/modifier', [
            'equipment_type' => [
                'label' => 'Équipement modifié',
                'code' => 'SHOULD_NOT_CHANGE',
                '_token' => $token,
            ],
        ]);

        self::assertResponseRedirects();
        $client->followRedirect();

        $updated = $repository->find($equipmentType->getId());
        self::assertSame($originalCode, $updated?->getCode());
    }

    public function testDeleteEquipmentTypeSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $equipmentType = (new EquipmentType())
            ->setCode('DELETE_EQUIPMENT')
            ->setLabel('Équipement à supprimer');
        $entityManager->persist($equipmentType);
        $entityManager->flush();

        $repository = self::getContainer()->get(EquipmentTypeRepository::class);
        $before = $repository->count([]);
        $token = $this->getDeleteToken($client);

        $client->request('POST', '/administration/parametrage/types-equipement/'.$equipmentType->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/types-equipement');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }

    public function testDeleteEquipmentTypeIsRejectedWhenUsed(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $venue = self::getContainer()->get(VenueRepository::class)->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $equipmentType = (new EquipmentType())
            ->setCode('USED_EQUIPMENT')
            ->setLabel('Équipement utilisé');

        $room = (new Room())
            ->setVenue($venue)
            ->setName('Salle test');

        $roomEquipment = (new RoomEquipment())
            ->setRoom($room)
            ->setEquipmentType($equipmentType)
            ->setIsIncluded(true);

        $entityManager->persist($equipmentType);
        $entityManager->persist($room);
        $entityManager->persist($roomEquipment);
        $entityManager->flush();

        $repository = $entityManager->getRepository(EquipmentType::class);
        $before = $repository->count([]);

        $token = $this->getDeleteToken($client);
        $client->request('POST', '/administration/parametrage/types-equipement/'.$equipmentType->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/types-equipement');

        $entityManager->clear();
        $after = $repository->count([]);
        self::assertSame($before, $after);
    }

    private function getDeleteToken($client): string
    {
        $client->request('GET', '/administration/parametrage/types-equipement');

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

        return $tokenManager->getToken('delete_equipment_type')->getValue();
    }
}
