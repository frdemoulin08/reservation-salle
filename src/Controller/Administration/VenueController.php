<?php

namespace App\Controller\Administration;

use App\Entity\SiteDocumentType;
use App\Entity\Venue;
use App\Entity\VenueDocument;
use App\Form\VenueDocumentUploadType;
use App\Form\VenuePhotoType;
use App\Form\VenueType;
use App\Repository\CountryRepository;
use App\Repository\SiteDocumentTypeRepository;
use App\Repository\VenueRepository;
use App\Service\SiteDocumentStorage;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN") or is_granted("ROLE_APP_MANAGER")'))]
class VenueController extends AbstractController
{
    #[Route('/administration/sites', name: 'app_admin_venues_index')]
    public function index(
        Request $request,
        VenueRepository $venueRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'updatedAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $venueRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['name', 'address.city', 'updatedAt'], 'v');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/venues/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/venues/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/sites/nouveau', name: 'app_admin_venues_new')]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $venue = new Venue();
        $form = $this->createForm(VenueType::class, $venue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($venue);
            $entityManager->flush();

            $this->addFlash('success', 'Le site a été créé avec succès.');

            return $this->redirectToRoute('app_admin_venues_show', [
                'publicIdentifier' => $venue->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/venues/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}', name: 'app_admin_venues_show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        string $publicIdentifier,
        VenueRepository $venueRepository,
        CountryRepository $countryRepository,
        SiteDocumentTypeRepository $documentTypeRepository,
        EntityManagerInterface $entityManager,
        SiteDocumentStorage $documentStorage,
    ): Response {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $photoType = $documentTypeRepository->findOneByCode(SiteDocumentType::CODE_PHOTO);
        $documentTypes = array_values(array_filter(
            $documentTypeRepository->findActiveOrdered(),
            static fn (SiteDocumentType $type) => SiteDocumentType::CODE_PHOTO !== $type->getCode()
        ));

        $photoForm = $this->createForm(VenuePhotoType::class, new VenueDocument(), [
            'action' => $this->generateUrl('app_admin_venues_show', ['publicIdentifier' => $publicIdentifier]),
        ]);
        $photoForm->handleRequest($request);

        if ($photoForm->isSubmitted() && $photoForm->isValid()) {
            if (!$photoType) {
                $photoForm->addError(new FormError('Le type de document photo est manquant.'));
            }
            $uploadedFiles = $photoForm->get('photo')->getData();
            $uploadedFiles = is_array($uploadedFiles) ? $uploadedFiles : ($uploadedFiles ? [$uploadedFiles] : []);
            if ($photoType && [] !== $uploadedFiles) {
                if (!$photoType->isMultipleAllowed() && ($this->hasDocumentOfType($venue, $photoType) || count($uploadedFiles) > 1)) {
                    $photoForm->addError(new FormError('Une seule photo est autorisée pour ce site.'));
                } else {
                    foreach ($uploadedFiles as $uploadedFile) {
                        if (!$uploadedFile instanceof UploadedFile) {
                            continue;
                        }

                        $originalName = $uploadedFile->getClientOriginalName();
                        $size = $uploadedFile->getSize();
                        $mimeType = $uploadedFile->getMimeType() ?? $uploadedFile->getClientMimeType();
                        $relativePath = $documentStorage->storeUploadedFile($venue, $uploadedFile, 'photos', $photoType->isPublic());
                        $label = pathinfo($originalName, PATHINFO_FILENAME) ?: 'Photo';

                        $photoDocument = (new VenueDocument())
                            ->setVenue($venue)
                            ->setLabel($label)
                            ->setFilePath($relativePath)
                            ->setMimeType($mimeType)
                            ->setOriginalFilename($originalName)
                            ->setSize($size)
                            ->setIsPublic($photoType->isPublic())
                            ->setDocumentType($photoType);

                        $entityManager->persist($photoDocument);
                    }
                    $entityManager->flush();

                    $this->addFlash('success', 'Les photos ont été ajoutées avec succès.');

                    return $this->redirectToRoute('app_admin_venues_show', [
                        'publicIdentifier' => $publicIdentifier,
                    ]);
                }
            }
        }

        $documentUpload = new VenueDocument();
        $documentForm = $this->createForm(VenueDocumentUploadType::class, $documentUpload, [
            'action' => $this->generateUrl('app_admin_venues_show', ['publicIdentifier' => $publicIdentifier]),
            'document_types' => $documentTypes,
        ]);
        $documentForm->handleRequest($request);

        if ($documentForm->isSubmitted() && $documentForm->isValid()) {
            $uploadedFile = $documentForm->get('file')->getData();
            $documentType = $documentUpload->getDocumentType();
            if (!$documentType instanceof SiteDocumentType) {
                $documentForm->addError(new FormError('Le type de document est obligatoire.'));
            } elseif (!$documentType->isMultipleAllowed() && $this->hasDocumentOfType($venue, $documentType)) {
                $documentForm->addError(new FormError('Un document de ce type existe déjà pour ce site.'));
            } elseif ($uploadedFile instanceof UploadedFile) {
                $originalName = $uploadedFile->getClientOriginalName();
                $size = $uploadedFile->getSize();
                $mimeType = $uploadedFile->getMimeType() ?? $uploadedFile->getClientMimeType();
                $relativePath = $documentStorage->storeUploadedFile($venue, $uploadedFile, 'documents', $documentType->isPublic());
                $label = trim($documentUpload->getLabel());
                if ('' === $label) {
                    $label = pathinfo($originalName, PATHINFO_FILENAME) ?: 'Document';
                    $documentUpload->setLabel($label);
                }

                $documentUpload
                    ->setVenue($venue)
                    ->setDocumentType($documentType)
                    ->setFilePath($relativePath)
                    ->setMimeType($mimeType)
                    ->setOriginalFilename($originalName)
                    ->setSize($size)
                    ->setIsPublic($documentType->isPublic());

                $entityManager->persist($documentUpload);
                $entityManager->flush();

                $this->addFlash('success', 'Le document a été ajouté avec succès.');

                return $this->redirectToRoute('app_admin_venues_show', [
                    'publicIdentifier' => $publicIdentifier,
                ]);
            }
        }

        $countryLabel = null;
        $countryCode = $venue->getAddress()?->getCountry();
        if ($countryCode) {
            $country = $countryRepository->findOneBy(['code' => $countryCode]);
            $countryLabel = $country?->getLabel();
        }

        $venuePhotos = $venue->getDocuments()->filter(
            static fn (VenueDocument $document) => SiteDocumentType::CODE_PHOTO === $document->getDocumentType()?->getCode()
        );
        $venueDocuments = $venue->getDocuments()->filter(
            static fn (VenueDocument $document) => SiteDocumentType::CODE_PHOTO !== $document->getDocumentType()?->getCode()
        );

        return $this->render('admin/venues/show.html.twig', [
            'venue' => $venue,
            'country_label' => $countryLabel,
            'photo_form' => $photoForm->createView(),
            'venue_photos' => $venuePhotos,
            'document_form' => $documentForm->createView(),
            'venue_documents' => $venueDocuments,
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/photos/{id}/supprimer', name: 'app_admin_venues_photo_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}', 'id' => '\\d+'], methods: ['POST'])]
    public function deletePhoto(
        Request $request,
        string $publicIdentifier,
        int $id,
        VenueRepository $venueRepository,
        EntityManagerInterface $entityManager,
        SiteDocumentStorage $documentStorage,
    ): Response {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $document = $entityManager->getRepository(VenueDocument::class)->find($id);
        if (!$document instanceof VenueDocument) {
            throw $this->createNotFoundException();
        }

        if ($document->getVenue()?->getId() !== $venue->getId() || SiteDocumentType::CODE_PHOTO !== $document->getDocumentType()?->getCode()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_venue_photo_'.$document->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_venues_show', [
                'publicIdentifier' => $publicIdentifier,
            ]);
        }

        $documentStorage->delete($document->getFilePath(), $document->isPublic());
        $entityManager->remove($document);
        $entityManager->flush();

        $this->addFlash('success', 'La photo a été supprimée.');

        return $this->redirectToRoute('app_admin_venues_show', [
            'publicIdentifier' => $publicIdentifier,
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/photos', name: 'app_admin_venues_photo_upload', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['POST'])]
    public function uploadPhotos(
        Request $request,
        string $publicIdentifier,
        VenueRepository $venueRepository,
        SiteDocumentTypeRepository $documentTypeRepository,
        SiteDocumentStorage $documentStorage,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        CsrfTokenManagerInterface $csrfTokenManager,
        Packages $packages,
    ): JsonResponse {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            return new JsonResponse(['message' => 'Site introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $token = (string) $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('upload_venue_photo_'.$publicIdentifier, $token))) {
            return new JsonResponse(['message' => 'Jeton CSRF invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $photoType = $documentTypeRepository->findOneByCode(SiteDocumentType::CODE_PHOTO);
        if (!$photoType) {
            return new JsonResponse(['message' => 'Type de photo introuvable.'], Response::HTTP_BAD_REQUEST);
        }

        $files = $request->files->get('photos');
        if (!$files) {
            return new JsonResponse(['message' => 'Aucune photo reçue.'], Response::HTTP_BAD_REQUEST);
        }

        $files = is_array($files) ? $files : [$files];

        if (!$photoType->isMultipleAllowed() && ($this->hasDocumentOfType($venue, $photoType) || count($files) > 1)) {
            return new JsonResponse(['message' => 'Une seule photo est autorisée pour ce site.'], Response::HTTP_BAD_REQUEST);
        }

        $constraint = new Image(
            maxSize: '5M',
            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
            mimeTypesMessage: 'Formats autorisés : JPG, PNG, WEBP.',
            maxSizeMessage: 'La photo ne doit pas dépasser 5 Mo.',
        );

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $violations = $validator->validate($file, $constraint);
            if (count($violations) > 0) {
                return new JsonResponse(['message' => $violations[0]->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $createdDocuments = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $originalName = $file->getClientOriginalName();
            $size = $file->getSize();
            $mimeType = $file->getMimeType() ?? $file->getClientMimeType();
            $relativePath = $documentStorage->storeUploadedFile($venue, $file, 'photos', $photoType->isPublic());
            $label = pathinfo($originalName, PATHINFO_FILENAME) ?: 'Photo';

            $photoDocument = (new VenueDocument())
                ->setVenue($venue)
                ->setLabel($label)
                ->setFilePath($relativePath)
                ->setMimeType($mimeType)
                ->setOriginalFilename($originalName)
                ->setSize($size)
                ->setIsPublic($photoType->isPublic())
                ->setDocumentType($photoType);

            $entityManager->persist($photoDocument);
            $createdDocuments[] = $photoDocument;
        }

        $entityManager->flush();

        $photos = [];
        foreach ($createdDocuments as $photoDocument) {
            $photos[] = [
                'id' => $photoDocument->getId(),
                'label' => $photoDocument->getLabel(),
                'url' => $packages->getUrl($photoDocument->getFilePath()),
                'createdAt' => $photoDocument->getCreatedAt()?->format('d/m/Y'),
                'deleteUrl' => $this->generateUrl('app_admin_venues_photo_delete', [
                    'publicIdentifier' => $publicIdentifier,
                    'id' => $photoDocument->getId(),
                ]),
                'deleteToken' => $csrfTokenManager->getToken('delete_venue_photo_'.$photoDocument->getId())->getValue(),
                'updateUrl' => $this->generateUrl('app_admin_venues_photo_label_update', [
                    'publicIdentifier' => $publicIdentifier,
                    'id' => $photoDocument->getId(),
                ]),
                'updateToken' => $csrfTokenManager->getToken('update_venue_photo_label_'.$photoDocument->getId())->getValue(),
            ];
        }

        return new JsonResponse([
            'message' => 'Photos ajoutées.',
            'photos' => $photos,
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/photos/{id}/libelle', name: 'app_admin_venues_photo_label_update', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}', 'id' => '\\d+'], methods: ['POST'])]
    public function updatePhotoLabel(
        Request $request,
        string $publicIdentifier,
        int $id,
        VenueRepository $venueRepository,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): JsonResponse {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            return new JsonResponse(['message' => 'Site introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $document = $entityManager->getRepository(VenueDocument::class)->find($id);
        if (!$document instanceof VenueDocument) {
            return new JsonResponse(['message' => 'Photo introuvable.'], Response::HTTP_NOT_FOUND);
        }

        if ($document->getVenue()?->getId() !== $venue->getId() || SiteDocumentType::CODE_PHOTO !== $document->getDocumentType()?->getCode()) {
            return new JsonResponse(['message' => 'Photo introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $token = (string) $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update_venue_photo_label_'.$document->getId(), $token))) {
            return new JsonResponse(['message' => 'Jeton CSRF invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $label = trim((string) $request->request->get('label', ''));
        if ('' === $label) {
            return new JsonResponse(['message' => 'Le libellé est obligatoire.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (mb_strlen($label) > 255) {
            return new JsonResponse(['message' => 'Le libellé est trop long.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $document->setLabel($label);
        $entityManager->flush();

        return new JsonResponse([
            'label' => $document->getLabel(),
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/documents/{id}/supprimer', name: 'app_admin_venues_document_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}', 'id' => '\\d+'], methods: ['POST'])]
    public function deleteDocument(
        Request $request,
        string $publicIdentifier,
        int $id,
        VenueRepository $venueRepository,
        EntityManagerInterface $entityManager,
        SiteDocumentStorage $documentStorage,
    ): Response {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $document = $entityManager->getRepository(VenueDocument::class)->find($id);
        if (!$document instanceof VenueDocument) {
            throw $this->createNotFoundException();
        }

        if ($document->getVenue()?->getId() !== $venue->getId()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_venue_document_'.$document->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_venues_show', [
                'publicIdentifier' => $publicIdentifier,
            ]);
        }

        $documentStorage->delete($document->getFilePath(), $document->isPublic());
        $entityManager->remove($document);
        $entityManager->flush();

        $this->addFlash('success', 'Le document a été supprimé.');

        return $this->redirectToRoute('app_admin_venues_show', [
            'publicIdentifier' => $publicIdentifier,
        ]);
    }

    #[Route('/administration/sites/documents/{id}/telecharger', name: 'app_admin_site_document_download', requirements: ['id' => '\\d+'])]
    public function downloadDocument(
        int $id,
        EntityManagerInterface $entityManager,
        SiteDocumentStorage $documentStorage,
    ): Response {
        $document = $entityManager->getRepository(VenueDocument::class)->find($id);
        if (!$document instanceof VenueDocument) {
            throw $this->createNotFoundException();
        }

        $stream = $documentStorage->readStream($document->getFilePath(), $document->isPublic());
        $response = new StreamedResponse(static function () use ($stream): void {
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        });

        $filename = $document->getOriginalFilename() ?: $document->getLabel() ?: 'document';
        $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);

        $response->headers->set('Content-Type', $document->getMimeType() ?? 'application/octet-stream');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function hasDocumentOfType(Venue $venue, SiteDocumentType $documentType): bool
    {
        return $venue->getDocuments()->exists(
            static fn (int $index, VenueDocument $document) => $document->getDocumentType()?->getId() === $documentType->getId()
        );
    }

    #[Route('/administration/sites/{publicIdentifier}/modifier', name: 'app_admin_venues_edit', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function edit(Request $request, string $publicIdentifier, VenueRepository $venueRepository, EntityManagerInterface $entityManager): Response
    {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(VenueType::class, $venue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le site a été mis à jour.');

            return $this->redirectToRoute('app_admin_venues_show', [
                'publicIdentifier' => $venue->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/venues/edit.html.twig', [
            'venue' => $venue,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/supprimer', name: 'app_admin_venues_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function delete(Request $request, string $publicIdentifier, VenueRepository $venueRepository, EntityManagerInterface $entityManager): Response
    {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_venue', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_venues_index');
        }

        $entityManager->remove($venue);
        $entityManager->flush();
        $this->addFlash('success', 'Le site a été supprimé.');

        return $this->redirectToRoute('app_admin_venues_index');
    }
}
