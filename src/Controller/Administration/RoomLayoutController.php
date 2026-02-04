<?php

namespace App\Controller\Administration;

use App\Entity\RoomLayout;
use App\Form\RoomLayoutType;
use App\Repository\RoomLayoutRepository;
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
class RoomLayoutController extends AbstractController
{
    #[Route('/administration/parametrage/configurations-salle', name: 'app_admin_room_layouts_index')]
    public function index(
        Request $request,
        RoomLayoutRepository $roomLayoutRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'label',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $roomLayoutRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label', 'code'], 'rl');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/room_layouts/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/room_layouts/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/configurations-salle/nouveau', name: 'app_admin_room_layouts_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $roomLayout = new RoomLayout();
        $form = $this->createForm(RoomLayoutType::class, $roomLayout);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($roomLayout);
            $entityManager->flush();

            $this->addFlash('success', 'La configuration de salle a été créée avec succès.');

            return $this->redirectToRoute('app_admin_room_layouts_index');
        }

        return $this->render('admin/room_layouts/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/configurations-salle/{id}', name: 'app_admin_room_layouts_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, RoomLayoutRepository $roomLayoutRepository): Response
    {
        $roomLayout = $roomLayoutRepository->find($id);
        if (!$roomLayout instanceof RoomLayout) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/room_layouts/show.html.twig', [
            'room_layout' => $roomLayout,
        ]);
    }

    #[Route('/administration/parametrage/configurations-salle/{id}/modifier', name: 'app_admin_room_layouts_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        RoomLayoutRepository $roomLayoutRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $roomLayout = $roomLayoutRepository->find($id);
        if (!$roomLayout instanceof RoomLayout) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(RoomLayoutType::class, $roomLayout, [
            'code_disabled' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'La configuration de salle a été mise à jour.');

            return $this->redirectToRoute('app_admin_room_layouts_index');
        }

        return $this->render('admin/room_layouts/edit.html.twig', [
            'room_layout' => $roomLayout,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/configurations-salle/{id}/supprimer', name: 'app_admin_room_layouts_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        RoomLayoutRepository $roomLayoutRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $roomLayout = $roomLayoutRepository->find($id);
        if (!$roomLayout instanceof RoomLayout) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_room_layout', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_room_layouts_index');
        }

        if ($roomLayout->getRooms()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer une configuration de salle déjà utilisée.');

            return $this->redirectToRoute('app_admin_room_layouts_index');
        }

        $entityManager->remove($roomLayout);
        $entityManager->flush();
        $this->addFlash('success', 'La configuration de salle a été supprimée.');

        return $this->redirectToRoute('app_admin_room_layouts_index');
    }
}
