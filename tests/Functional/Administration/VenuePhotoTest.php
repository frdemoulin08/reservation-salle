<?php

namespace App\Tests\Functional\Administration;

use App\Entity\SiteDocumentType;
use App\Entity\VenueDocument;
use App\Repository\VenueRepository;
use App\Tests\Functional\DatabaseWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class VenuePhotoTest extends DatabaseWebTestCase
{
    public function testUploadPhotoSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $venue = self::getContainer()->get(VenueRepository::class)->findOneBy([]);

        self::assertNotNull($venue, 'Aucun site disponible pour le test.');

        $crawler = $client->request('GET', '/administration/sites/'.$venue->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $token = (string) $crawler->filter('[data-photo-dropzone]')->attr('data-csrf-token');
        self::assertNotSame('', $token, 'Jeton CSRF manquant pour l’upload.');

        $tempPath = $this->createTempImage();

        try {
            $uploadedFile = new UploadedFile($tempPath, 'photo.png', 'image/png', null, true);

            $client->request('POST', '/administration/sites/'.$venue->getPublicIdentifier().'/photos', [
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

            $photoId = $payload['photos'][0]['id'] ?? null;
            self::assertNotNull($photoId);

            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $document = $entityManager->getRepository(VenueDocument::class)->find($photoId);
            self::assertNotNull($document);

            $publicPath = self::getContainer()->getParameter('kernel.project_dir').'/public/'.$document->getFilePath();
            self::assertFileExists($publicPath);

            if (is_file($publicPath)) {
                unlink($publicPath);
            }
        } finally {
            if (is_file($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function testUpdatePhotoLabelSucceeds(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $photo = $entityManager->getRepository(VenueDocument::class)
            ->createQueryBuilder('document')
            ->join('document.documentType', 'type')
            ->where('type.code = :code')
            ->setParameter('code', SiteDocumentType::CODE_PHOTO)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        self::assertNotNull($photo, 'Aucune photo disponible pour le test.');
        self::assertNotNull($photo->getVenue());

        $crawler = $client->request('GET', '/administration/sites/'.$photo->getVenue()->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $token = (string) $crawler
            ->filter('[data-photo-id="'.$photo->getId().'"] [data-photo-label-card]')
            ->attr('data-csrf-token');
        self::assertNotSame('', $token, 'Jeton CSRF manquant pour la mise à jour du libellé.');

        $client->request('POST', '/administration/sites/'.$photo->getVenue()->getPublicIdentifier().'/photos/'.$photo->getId().'/libelle', [
            'label' => 'Nouvelle photo',
            '_token' => $token,
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseIsSuccessful();

        $payload = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('Nouvelle photo', $payload['label'] ?? null);

        $updated = $entityManager->getRepository(VenueDocument::class)->find($photo->getId());
        self::assertNotNull($updated);
        self::assertSame('Nouvelle photo', $updated->getLabel());
    }

    public function testUpdatePhotoLabelRejectsEmptyLabel(): void
    {
        $client = $this->loginAsAdmin();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $photo = $entityManager->getRepository(VenueDocument::class)
            ->createQueryBuilder('document')
            ->join('document.documentType', 'type')
            ->where('type.code = :code')
            ->setParameter('code', SiteDocumentType::CODE_PHOTO)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        self::assertNotNull($photo, 'Aucune photo disponible pour le test.');
        self::assertNotNull($photo->getVenue());

        $crawler = $client->request('GET', '/administration/sites/'.$photo->getVenue()->getPublicIdentifier());
        self::assertResponseIsSuccessful();

        $token = (string) $crawler
            ->filter('[data-photo-id="'.$photo->getId().'"] [data-photo-label-card]')
            ->attr('data-csrf-token');
        self::assertNotSame('', $token, 'Jeton CSRF manquant pour la mise à jour du libellé.');

        $client->request('POST', '/administration/sites/'.$photo->getVenue()->getPublicIdentifier().'/photos/'.$photo->getId().'/libelle', [
            'label' => '   ',
            '_token' => $token,
        ], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);

        $payload = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertIsArray($payload);
        self::assertSame('Le libellé est obligatoire.', $payload['message'] ?? null);
    }

    private function createTempImage(): string
    {
        $data = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMB/8g2fX8AAAAASUVORK5CYII='
        );

        $path = tempnam(sys_get_temp_dir(), 'venue_photo_');
        if (false === $path) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire.');
        }

        file_put_contents($path, $data);

        return $path;
    }
}
