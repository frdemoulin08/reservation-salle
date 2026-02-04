<?php

namespace App\Controller\Administration;

use App\Entity\EquipmentType;
use App\Form\EquipmentTypeType;
use App\Repository\EquipmentTypeRepository;
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
class EquipmentTypeController extends AbstractController
{
    #[Route('/administration/parametrage/types-equipement', name: 'app_admin_equipment_types_index')]
    public function index(
        Request $request,
        EquipmentTypeRepository $equipmentTypeRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'label',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $equipmentTypeRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label', 'code', 'category'], 'et');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/equipment_types/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/equipment_types/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/types-equipement/nouveau', name: 'app_admin_equipment_types_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $equipmentType = new EquipmentType();
        $form = $this->createForm(EquipmentTypeType::class, $equipmentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($equipmentType);
            $entityManager->flush();

            $this->addFlash('success', 'Le type d’équipement a été créé avec succès.');

            return $this->redirectToRoute('app_admin_equipment_types_index');
        }

        return $this->render('admin/equipment_types/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-equipement/{id}', name: 'app_admin_equipment_types_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, EquipmentTypeRepository $equipmentTypeRepository): Response
    {
        $equipmentType = $equipmentTypeRepository->find($id);
        if (!$equipmentType instanceof EquipmentType) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/equipment_types/show.html.twig', [
            'equipment_type' => $equipmentType,
        ]);
    }

    #[Route('/administration/parametrage/types-equipement/{id}/modifier', name: 'app_admin_equipment_types_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        EquipmentTypeRepository $equipmentTypeRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $equipmentType = $equipmentTypeRepository->find($id);
        if (!$equipmentType instanceof EquipmentType) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(EquipmentTypeType::class, $equipmentType, [
            'code_disabled' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le type d’équipement a été mis à jour.');

            return $this->redirectToRoute('app_admin_equipment_types_index');
        }

        return $this->render('admin/equipment_types/edit.html.twig', [
            'equipment_type' => $equipmentType,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-equipement/{id}/supprimer', name: 'app_admin_equipment_types_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        EquipmentTypeRepository $equipmentTypeRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $equipmentType = $equipmentTypeRepository->find($id);
        if (!$equipmentType instanceof EquipmentType) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_equipment_type', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_equipment_types_index');
        }

        if ($equipmentType->getRoomEquipments()->count() > 0 || $equipmentType->getVenueEquipments()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer un type d’équipement déjà utilisé.');

            return $this->redirectToRoute('app_admin_equipment_types_index');
        }

        $entityManager->remove($equipmentType);
        $entityManager->flush();
        $this->addFlash('success', 'Le type d’équipement a été supprimé.');

        return $this->redirectToRoute('app_admin_equipment_types_index');
    }
}
