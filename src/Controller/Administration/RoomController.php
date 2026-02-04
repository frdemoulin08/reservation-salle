<?php

namespace App\Controller\Administration;

use App\Entity\Room;
use App\Form\RoomFormType;
use App\Repository\RoomRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN") or is_granted("ROLE_APP_MANAGER")'))]
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
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();

            $this->addFlash('success', 'La salle a été créée avec succès.');

            return $this->redirectToRoute('app_admin_rooms_show', [
                'id' => $room->getId(),
            ]);
        }

        return $this->render('admin/rooms/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/salles/{id}', name: 'app_admin_rooms_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, RoomRepository $roomRepository): Response
    {
        $room = $roomRepository->find($id);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/rooms/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/administration/salles/{id}/modifier', name: 'app_admin_rooms_edit', requirements: ['id' => '\\d+'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function edit(
        Request $request,
        int $id,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $room = $roomRepository->find($id);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomFormType::class, $room);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La salle a été mise à jour.');

            return $this->redirectToRoute('app_admin_rooms_show', [
                'id' => $room->getId(),
            ]);
        }

        return $this->render('admin/rooms/edit.html.twig', [
            'room' => $room,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/salles/{id}/supprimer', name: 'app_admin_rooms_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    #[IsGranted(new Expression('is_granted("ROLE_BUSINESS_ADMIN")'))]
    public function delete(
        Request $request,
        int $id,
        RoomRepository $roomRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $room = $roomRepository->find($id);
        if (!$room instanceof Room) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_room', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_rooms_index');
        }

        $hasDependencies = $room->getRoomEquipments()->count() > 0
            || $room->getRoomServices()->count() > 0
            || $room->getRoomUsages()->count() > 0
            || $room->getRoomDocuments()->count() > 0
            || $room->getRoomPricings()->count() > 0
            || $room->getReservations()->count() > 0;

        if ($hasDependencies) {
            $this->addFlash('error', 'Impossible de supprimer une salle déjà utilisée.');

            return $this->redirectToRoute('app_admin_rooms_show', ['id' => $room->getId()]);
        }

        $entityManager->remove($room);
        $entityManager->flush();
        $this->addFlash('success', 'La salle a été supprimée.');

        return $this->redirectToRoute('app_admin_rooms_index');
    }
}
