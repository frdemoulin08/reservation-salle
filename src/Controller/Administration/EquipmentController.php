<?php

namespace App\Controller\Administration;

use App\Entity\Equipment;
use App\Form\EquipmentFormType;
use App\Repository\EquipmentRepository;
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
class EquipmentController extends AbstractController
{
    #[Route('/administration/parametrage/equipements', name: 'app_admin_equipments_index')]
    public function index(
        Request $request,
        EquipmentRepository $equipmentRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'label',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $equipmentRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label'], 'e');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/equipments/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/equipments/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/equipements/nouveau', name: 'app_admin_equipments_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipment = new Equipment();
        $form = $this->createForm(EquipmentFormType::class, $equipment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipment);
            $entityManager->flush();

            $this->addFlash('success', 'L’équipement a été créé avec succès.');

            return $this->redirectToRoute('app_admin_equipments_index');
        }

        return $this->render('admin/equipments/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/equipements/{id}', name: 'app_admin_equipments_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, EquipmentRepository $equipmentRepository): Response
    {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment instanceof Equipment) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/equipments/show.html.twig', [
            'equipment' => $equipment,
        ]);
    }

    #[Route('/administration/parametrage/equipements/{id}/modifier', name: 'app_admin_equipments_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        EquipmentRepository $equipmentRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment instanceof Equipment) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EquipmentFormType::class, $equipment, [
            'data_class' => Equipment::class,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'L’équipement a été mis à jour.');

            return $this->redirectToRoute('app_admin_equipments_show', [
                'id' => $equipment->getId(),
            ]);
        }

        return $this->render('admin/equipments/edit.html.twig', [
            'equipment' => $equipment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/equipements/{id}/supprimer', name: 'app_admin_equipments_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        EquipmentRepository $equipmentRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $equipment = $equipmentRepository->find($id);
        if (!$equipment instanceof Equipment) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_equipment', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_equipments_index');
        }

        $entityManager->remove($equipment);
        $entityManager->flush();

        $this->addFlash('success', 'L’équipement a été supprimé.');

        return $this->redirectToRoute('app_admin_equipments_index');
    }
}
