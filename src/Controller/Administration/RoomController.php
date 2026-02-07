<?php

namespace App\Controller\Administration;

use App\Entity\Room;
use App\Entity\RoomDocument;
use App\Form\PhotoUploadType;
use App\Form\RoomFormType;
use App\Repository\RoomRepository;
use App\Service\PhotoUploadHelper;
use App\Table\TablePaginator;
use App\Table\TableParams;
use App\UseCase\Room\AddRoomPhotos;
use App\UseCase\Room\CreateRoom;
use App\UseCase\Room\DeleteRoom;
use App\UseCase\Room\DeleteRoomPhoto;
use App\UseCase\Room\UpdateRoom;
use App\UseCase\Room\UpdateRoomPhotoLabel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN") or is_granted("ROLE_APP_MANAGER")'))]
class RoomController extends AbstractController
{
    #[Route('/administration/salles', name: 'app_admin_rooms_index')]
    public function index(
        Request $request,
        RoomRepository $roomRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'name',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $roomRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['name', 'seatedCapacity', 'standingCapacity'], 'r');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/rooms/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/rooms/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/salles/nouveau', name: 'app_admin_rooms_new')]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function new(Request $request, CreateRoom $createRoom): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createRoom->execute($room);

            $this->addFlash('success', 'La salle a été créée avec succès.');

            return $this->redirectToRoute('app_admin_rooms_show', [
                'publicIdentifier' => $room->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/rooms/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/salles/{publicIdentifier}', name: 'app_admin_rooms_show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function show(string $publicIdentifier, RoomRepository $roomRepository): Response
    {
        $room = $roomRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/rooms/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/administration/salles/{publicIdentifier}/modifier', name: 'app_admin_rooms_edit', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function edit(
        Request $request,
        string $publicIdentifier,
        RoomRepository $roomRepository,
        PhotoUploadHelper $photoUploadHelper,
        AddRoomPhotos $addRoomPhotos,
        UpdateRoom $updateRoom,
    ): Response {
        $room = $roomRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        $photoForm = $this->createForm(PhotoUploadType::class, new RoomDocument(), [
            'action' => $this->generateUrl('app_admin_rooms_edit', ['publicIdentifier' => $room->getPublicIdentifier()]),
            'data_class' => RoomDocument::class,
        ]);
        $photoForm->handleRequest($request);

        if ($photoForm->isSubmitted() && $photoForm->isValid()) {
            $uploadedFiles = $photoUploadHelper->normalizeFiles($photoForm->get('photo')->getData());
            if ([] !== $uploadedFiles) {
                $createdDocuments = $addRoomPhotos->execute($room, $uploadedFiles);
                if ([] !== $createdDocuments) {
                    $this->addFlash('success', $photoUploadHelper->getPhotosAddedMessage());

                    return $this->redirectToRoute('app_admin_rooms_edit', [
                        'publicIdentifier' => $room->getPublicIdentifier(),
                    ]);
                }
            }
        }

        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updateRoom->execute();
            $this->addFlash('success', 'La salle a été mise à jour.');

            return $this->redirectToRoute('app_admin_rooms_show', [
                'publicIdentifier' => $room->getPublicIdentifier(),
            ]);
        }

        $roomPhotos = $room->getRoomDocuments()->filter(
            static fn (RoomDocument $document) => RoomDocument::TYPE_PHOTO === $document->getType()
        );

        return $this->render('admin/rooms/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
            'photo_form' => $photoForm->createView(),
            'room_photos' => $roomPhotos,
        ]);
    }

    #[Route('/administration/salles/{publicIdentifier}/supprimer', name: 'app_admin_rooms_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function delete(
        Request $request,
        string $publicIdentifier,
        RoomRepository $roomRepository,
        DeleteRoom $deleteRoom,
    ): Response {
        $room = $roomRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_room', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_rooms_index');
        }

        if (!$deleteRoom->execute($room)) {
            $this->addFlash('error', 'Impossible de supprimer une salle déjà utilisée.');

            return $this->redirectToRoute('app_admin_rooms_show', [
                'publicIdentifier' => $room->getPublicIdentifier(),
            ]);
        }

        $this->addFlash('success', 'La salle a été supprimée.');

        return $this->redirectToRoute('app_admin_rooms_index');
    }

    #[Route('/administration/salles/{publicIdentifier}/photos', name: 'app_admin_rooms_photo_upload', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function uploadPhotos(
        Request $request,
        string $publicIdentifier,
        RoomRepository $roomRepository,
        CsrfTokenManagerInterface $csrfTokenManager,
        Packages $packages,
        PhotoUploadHelper $photoUploadHelper,
        AddRoomPhotos $addRoomPhotos,
    ): JsonResponse {
        $room = $roomRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$room instanceof Room) {
            return new JsonResponse(['message' => $photoUploadHelper->getRoomNotFoundMessage()], Response::HTTP_NOT_FOUND);
        }

        $token = (string) $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('upload_room_photo_'.$publicIdentifier, $token))) {
            return new JsonResponse(['message' => $photoUploadHelper->getInvalidCsrfMessage()], Response::HTTP_BAD_REQUEST);
        }

        $files = $photoUploadHelper->normalizeFiles($request->files->get('photos'));
        if ([] === $files) {
            return new JsonResponse(['message' => $photoUploadHelper->getNoFilesMessage()], Response::HTTP_BAD_REQUEST);
        }

        $validationError = $photoUploadHelper->validateFiles($files);
        if (null !== $validationError) {
            return new JsonResponse(['message' => $validationError], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $createdDocuments = $addRoomPhotos->execute($room, $files);

        $photos = [];
        foreach ($createdDocuments as $photoDocument) {
            $photos[] = [
                'id' => $photoDocument->getId(),
                'label' => $photoDocument->getLabel(),
                'url' => $packages->getUrl($photoDocument->getFilePath()),
                'createdAt' => $photoDocument->getCreatedAt()?->format('d/m/Y'),
                'deleteUrl' => $this->generateUrl('app_admin_rooms_photo_delete', [
                    'publicIdentifier' => $room->getPublicIdentifier(),
                    'photoId' => $photoDocument->getId(),
                ]),
                'deleteToken' => $csrfTokenManager->getToken('delete_room_photo_'.$photoDocument->getId())->getValue(),
                'updateUrl' => $this->generateUrl('app_admin_rooms_photo_label_update', [
                    'publicIdentifier' => $room->getPublicIdentifier(),
                    'photoId' => $photoDocument->getId(),
                ]),
                'updateToken' => $csrfTokenManager->getToken('update_room_photo_label_'.$photoDocument->getId())->getValue(),
            ];
        }

        return new JsonResponse([
            'message' => $photoUploadHelper->getPhotosAddedShortMessage(),
            'photos' => $photos,
        ]);
    }

    #[Route('/administration/salles/{publicIdentifier}/photos/{photoId}/supprimer', name: 'app_admin_rooms_photo_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}', 'photoId' => '\\d+'], methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function deletePhoto(
        Request $request,
        string $publicIdentifier,
        int $photoId,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        PhotoUploadHelper $photoUploadHelper,
        DeleteRoomPhoto $deleteRoomPhoto,
    ): Response {
        $room = $roomRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        $document = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        if (!$document instanceof RoomDocument) {
            throw $this->createNotFoundException();
        }

        if ($document->getRoom()?->getId() !== $room->getId() || RoomDocument::TYPE_PHOTO !== $document->getType()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_room_photo_'.$document->getId(), (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_rooms_edit', [
                'publicIdentifier' => $room->getPublicIdentifier(),
            ]);
        }

        $deleteRoomPhoto->execute($document);

        $this->addFlash('success', $photoUploadHelper->getPhotoDeletedMessage());

        return $this->redirectToRoute('app_admin_rooms_edit', [
            'publicIdentifier' => $room->getPublicIdentifier(),
        ]);
    }

    #[Route('/administration/salles/{publicIdentifier}/photos/{photoId}/libelle', name: 'app_admin_rooms_photo_label_update', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}', 'photoId' => '\\d+'], methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function updatePhotoLabel(
        Request $request,
        string $publicIdentifier,
        int $photoId,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        PhotoUploadHelper $photoUploadHelper,
        UpdateRoomPhotoLabel $updateRoomPhotoLabel,
    ): JsonResponse {
        $room = $roomRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$room instanceof Room) {
            return new JsonResponse(['message' => $photoUploadHelper->getRoomNotFoundMessage()], Response::HTTP_NOT_FOUND);
        }

        $document = $entityManager->getRepository(RoomDocument::class)->find($photoId);
        if (!$document instanceof RoomDocument) {
            return new JsonResponse(['message' => $photoUploadHelper->getPhotoNotFoundMessage()], Response::HTTP_NOT_FOUND);
        }

        if ($document->getRoom()?->getId() !== $room->getId() || RoomDocument::TYPE_PHOTO !== $document->getType()) {
            return new JsonResponse(['message' => $photoUploadHelper->getPhotoNotFoundMessage()], Response::HTTP_NOT_FOUND);
        }

        $token = (string) $request->request->get('_token');
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('update_room_photo_label_'.$document->getId(), $token))) {
            return new JsonResponse(['message' => $photoUploadHelper->getInvalidCsrfMessage()], Response::HTTP_BAD_REQUEST);
        }

        $error = $updateRoomPhotoLabel->execute($document, (string) $request->request->get('label', ''));
        if (null !== $error) {
            return new JsonResponse(['message' => $error], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse([
            'label' => $document->getLabel(),
        ]);
    }
}
