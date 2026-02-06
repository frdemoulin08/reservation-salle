<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Equipment;
use App\Repository\EquipmentRepository;
use App\Repository\EquipmentTypeRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class EquipmentCrudTest extends DatabaseWebTestCase
{
    public function testIndexListsEquipments(): void
    {
        $client = $this->loginAsBusinessAdmin();

        $client->request('GET', '/administration/parametrage/equipements');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('table', 'Mange-debout');
    }

    public function testCreateEquipmentSucceeds(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $type = self::getContainer()->get(EquipmentTypeRepository::class)->findOneBy([]);

        self::assertNotNull($type, 'Aucun type d’équipement disponible pour le test.');

        $crawler = $client->request('GET', '/administration/parametrage/equipements/nouveau');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Créer l’équipement')->form();
        $form['equipment[label]'] = 'Équipement test';
        $form['equipment[equipmentType]'] = $type->getId();

        $client->submit($form);

        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = self::getContainer()->get(EquipmentRepository::class);
        self::assertNotNull($repository->findOneBy(['label' => 'Équipement test']));
    }

    public function testEditEquipmentSucceeds(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $type = self::getContainer()->get(EquipmentTypeRepository::class)->findOneBy([]);

        self::assertNotNull($type, 'Aucun type d’équipement disponible pour le test.');

        $equipment = (new Equipment())
            ->setLabel('Équipement à éditer')
            ->setEquipmentType($type);
        $entityManager->persist($equipment);
        $entityManager->flush();

        $crawler = $client->request('GET', '/administration/parametrage/equipements/'.$equipment->getId().'/modifier');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Enregistrer')->form();
        $form['equipment[label]'] = 'Équipement mis à jour';

        $client->submit($form);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertResponseIsSuccessful();

        $repository = $entityManager->getRepository(Equipment::class);
        $updated = $repository->find($equipment->getId());
        self::assertSame('Équipement mis à jour', $updated?->getLabel());
    }

    public function testDeleteEquipmentSucceeds(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $type = self::getContainer()->get(EquipmentTypeRepository::class)->findOneBy([]);

        self::assertNotNull($type, 'Aucun type d’équipement disponible pour le test.');

        $equipment = (new Equipment())
            ->setLabel('Équipement à supprimer')
            ->setEquipmentType($type);
        $entityManager->persist($equipment);
        $entityManager->flush();

        $repository = self::getContainer()->get(EquipmentRepository::class);
        $before = $repository->count([]);

        $token = $this->getDeleteToken($client);

        $client->request('POST', '/administration/parametrage/equipements/'.$equipment->getId().'/supprimer', [
            '_token' => $token,
        ]);

        self::assertResponseRedirects('/administration/parametrage/equipements');
        $after = $repository->count([]);
        self::assertSame($before - 1, $after);
    }

    private function getDeleteToken($client): string
    {
        $client->request('GET', '/administration/parametrage/equipements');

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

        return $tokenManager->getToken('delete_equipment')->getValue();
    }
}
