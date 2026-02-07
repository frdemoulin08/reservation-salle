<?php

namespace App\Controller\Administration;

use App\Entity\EventType;
use App\Form\EventTypeType;
use App\Repository\EventTypeRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use App\UseCase\EventType\CreateEventType;
use App\UseCase\EventType\DeleteEventType;
use App\UseCase\EventType\UpdateEventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN")'))]
class EventTypeController extends AbstractController
{
    #[Route('/administration/parametrage/types-evenement', name: 'app_admin_event_types_index')]
    public function index(
        Request $request,
        EventTypeRepository $eventTypeRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'label',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $eventTypeRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label', 'code'], 'et');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/event_types/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/event_types/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/types-evenement/nouveau', name: 'app_admin_event_types_new')]
    public function new(Request $request, CreateEventType $createEventType): Response
    {
        $eventType = new EventType();
        $form = $this->createForm(EventTypeType::class, $eventType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createEventType->execute($eventType);

            $this->addFlash('success', 'Le type d’événement a été créé avec succès.');

            return $this->redirectToRoute('app_admin_event_types_index');
        }

        return $this->render('admin/event_types/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-evenement/{id}', name: 'app_admin_event_types_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, EventTypeRepository $eventTypeRepository): Response
    {
        $eventType = $eventTypeRepository->find($id);
        if (!$eventType instanceof EventType) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/event_types/show.html.twig', [
            'event_type' => $eventType,
        ]);
    }

    #[Route('/administration/parametrage/types-evenement/{id}/modifier', name: 'app_admin_event_types_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        EventTypeRepository $eventTypeRepository,
        UpdateEventType $updateEventType,
    ): Response {
        $eventType = $eventTypeRepository->find($id);
        if (!$eventType instanceof EventType) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EventTypeType::class, $eventType, [
            'code_disabled' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updateEventType->execute($eventType);
            $this->addFlash('success', 'Le type d’événement a été mis à jour.');

            return $this->redirectToRoute('app_admin_event_types_index');
        }

        return $this->render('admin/event_types/edit.html.twig', [
            'event_type' => $eventType,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-evenement/{id}/supprimer', name: 'app_admin_event_types_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        EventTypeRepository $eventTypeRepository,
        DeleteEventType $deleteEventType,
    ): Response {
        $eventType = $eventTypeRepository->find($id);
        if (!$eventType instanceof EventType) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_event_type', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_event_types_index');
        }

        if (!$deleteEventType->execute($eventType)) {
            $this->addFlash('error', 'Impossible de supprimer un type d’événement déjà utilisé.');

            return $this->redirectToRoute('app_admin_event_types_index');
        }
        $this->addFlash('success', 'Le type d’événement a été supprimé.');

        return $this->redirectToRoute('app_admin_event_types_index');
    }
}
