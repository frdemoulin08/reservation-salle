<?php

namespace App\Tests\Functional\Administration;

use App\Entity\Room;
use App\Entity\RoomDocument;
use App\Entity\Venue;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class RoomPhotoTest extends DatabaseWebTestCase
{
    public function testUploadPhotoSucceeds(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        [$photoId] = $this->uploadRoomPhoto($client, $room);

        $document = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        self::assertNotNull($document);

        $publicPath = self::getContainer()->getParameter('kernel.project_dir').'/public/'.$document->getFilePath();
        self::assertFileExists($publicPath);

        if (is_file($publicPath)) {
            unlink($publicPath);
        }
    }

    public function testUpdatePhotoLabelSucceeds(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        [$photoId, $photoData] = $this->uploadRoomPhoto($client, $room);

        $updateUrl = $photoData['updateUrl'] ?? null;
        $updateToken = $photoData['updateToken'] ?? null;

        self::assertNotNull($updateUrl);
        self::assertNotNull($updateToken);

        $client->request('POST', $updateUrl, [
            'label' => 'Nouvelle photo',
            '_token' => $updateToken,
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('Nouvelle photo', $payload['label'] ?? null);

        $updated = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        self::assertNotNull($updated);
        self::assertSame('Nouvelle photo', $updated->getLabel());

        $publicPath = self::getContainer()->getParameter('kernel.project_dir').'/public/'.$updated->getFilePath();
        if (is_file($publicPath)) {
            unlink($publicPath);
        }
    }

    public function testUpdatePhotoLabelRejectsEmptyLabel(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        [, $photoData] = $this->uploadRoomPhoto($client, $room);

        $updateUrl = $photoData['updateUrl'] ?? null;
        $updateToken = $photoData['updateToken'] ?? null;
        $photoId = $photoData['id'] ?? null;

        self::assertNotNull($updateUrl);
        self::assertNotNull($updateToken);
        self::assertNotNull($photoId);

        $client->request('POST', $updateUrl, [
            'label' => '   ',
            '_token' => $updateToken,
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $payload = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('Le libellé est obligatoire.', $payload['message'] ?? null);

        $document = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        if ($document instanceof RoomDocument) {
            $publicPath = self::getContainer()->getParameter('kernel.project_dir').'/public/'.$document->getFilePath();
            if (is_file($publicPath)) {
                unlink($publicPath);
            }
        }
    }

    public function testDeletePhotoSucceeds(): void
    {
        $client = $this->loginAsBusinessAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $room = $this->createRoom($entityManager);

        [$photoId, $photoData] = $this->uploadRoomPhoto($client, $room);

        $deleteUrl = $photoData['deleteUrl'] ?? null;
        $deleteToken = $photoData['deleteToken'] ?? null;

        self::assertNotNull($deleteUrl);
        self::assertNotNull($deleteToken);

        $document = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        self::assertNotNull($document);

        $publicPath = self::getContainer()->getParameter('kernel.project_dir').'/public/'.$document->getFilePath();
        self::assertFileExists($publicPath);

        $client->request('POST', $deleteUrl, [
            '_token' => $deleteToken,
        ]);

        self::assertResponseRedirects();

        $entityManager->clear();
        $deleted = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        self::assertNull($deleted);
        self::assertFileDoesNotExist($publicPath);
    }

    /**
     * @return array{0: int, 1: array<string, mixed>}
     */
    private function uploadRoomPhoto($client, Room $room): array
    {
        $crawler = $client->request('GET', '/administration/salles/'.$room->getPublicIdentifier().'/modifier');
        self::assertResponseIsSuccessful();

        $token = (string) $crawler->filter('[data-photo-dropzone]')->attr('data-csrf-token');
        self::assertNotSame('', $token, 'Jeton CSRF manquant pour l’upload.');

        $tempPath = $this->createTempImage();

        try {
            $uploadedFile = new UploadedFile($tempPath, 'photo.png', 'image/png', null, true);

            $client->request('POST', '/administration/salles/'.$room->getPublicIdentifier().'/photos', [
                '_token' => $token,
            ], [
                'photos' => [$uploadedFile],
            ], [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
            ]);

            self::assertResponseIsSuccessful();

            $payload = json_decode((string) $client->getResponse()->getContent(), true);
            self::assertIsArray($payload);
            self::assertArrayHasKey('photos', $payload);
            self::assertCount(1, $payload['photos']);

            $photoData = $payload['photos'][0] ?? null;
            self::assertIsArray($photoData);

            $photoId = $photoData['id'] ?? null;
            self::assertNotNull($photoId);

            return [$photoId, $photoData];
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }
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

    private function createTempImage(): string
    {
        $data = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/8g2fX8AAAAASUVORK5CYII='
        );

        $path = tempnam(sys_get_temp_dir(), 'room_photo_');
        if (false === $path) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire.');
        }

        file_put_contents($path, $data);

        return $path;
    }
}
