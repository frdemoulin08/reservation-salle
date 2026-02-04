<?php

namespace App\Controller\Administration;

use App\Entity\RoomType;
use App\Form\RoomTypeType;
use App\Repository\RoomTypeRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN")'))]
class RoomTypeController extends AbstractController
{
    #[Route('/administration/parametrage/types-salle', name: 'app_admin_room_types_index')]
    public function index(
        Request $request,
        RoomTypeRepository $roomTypeRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'label',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $roomTypeRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label', 'code'], 'rt');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/room_types/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/room_types/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/types-salle/nouveau', name: 'app_admin_room_types_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $roomType = new RoomType();
        $form = $this->createForm(RoomTypeType::class, $roomType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($roomType);
            $entityManager->flush();

            $this->addFlash('success', 'Le type de salle a été créé avec succès.');

            return $this->redirectToRoute('app_admin_room_types_index');
        }

        return $this->render('admin/room_types/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-salle/{id}', name: 'app_admin_room_types_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, RoomTypeRepository $roomTypeRepository): Response
    {
        $roomType = $roomTypeRepository->find($id);
        if (!$roomType instanceof RoomType) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/room_types/show.html.twig', [
            'room_type' => $roomType,
        ]);
    }

    #[Route('/administration/parametrage/types-salle/{id}/modifier', name: 'app_admin_room_types_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        RoomTypeRepository $roomTypeRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $roomType = $roomTypeRepository->find($id);
        if (!$roomType instanceof RoomType) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomTypeType::class, $roomType, [
            'code_disabled' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le type de salle a été mis à jour.');

            return $this->redirectToRoute('app_admin_room_types_index');
        }

        return $this->render('admin/room_types/edit.html.twig', [
            'room_type' => $roomType,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-salle/{id}/supprimer', name: 'app_admin_room_types_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        RoomTypeRepository $roomTypeRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $roomType = $roomTypeRepository->find($id);
        if (!$roomType instanceof RoomType) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_room_type', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_room_types_index');
        }

        if ($roomType->getRooms()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer un type de salle déjà utilisé.');

            return $this->redirectToRoute('app_admin_room_types_index');
        }

        $entityManager->remove($roomType);
        $entityManager->flush();
        $this->addFlash('success', 'Le type de salle a été supprimé.');

        return $this->redirectToRoute('app_admin_room_types_index');
    }
}
