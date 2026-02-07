<?php

namespace App\Controller\Administration;

use App\Entity\Organization;
use App\Form\OrganizationType;
use App\Repository\OrganizationRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use App\UseCase\Organization\CreateOrganization;
use App\UseCase\Organization\DeleteOrganization;
use App\UseCase\Organization\UpdateOrganization;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_APP_MANAGER")'))]
class OrganizationController extends AbstractController
{
    #[Route('/administration/gestion/organisations', name: 'app_admin_organizations_index')]
    public function index(
        Request $request,
        OrganizationRepository $organizationRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'updatedAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $organizationRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['legalName', 'displayName', 'siret', 'organizationType', 'updatedAt'], 'o');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/organizations/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/organizations/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/gestion/organisations/nouveau', name: 'app_admin_organizations_new')]
    public function new(Request $request, CreateOrganization $createOrganization): Response
    {
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createOrganization->execute($organization);

            $this->addFlash('success', 'L\'organisation a été créée avec succès.');

            return $this->redirectToRoute('app_admin_organizations_show', [
                'id' => $organization->getId(),
            ]);
        }

        return $this->render('admin/organizations/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/gestion/organisations/{id}', name: 'app_admin_organizations_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, OrganizationRepository $organizationRepository): Response
    {
        $organization = $organizationRepository->find($id);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/organizations/show.html.twig', [
            'organization' => $organization,
        ]);
    }

    #[Route('/administration/gestion/organisations/{id}/modifier', name: 'app_admin_organizations_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        OrganizationRepository $organizationRepository,
        UpdateOrganization $updateOrganization,
    ): Response {
        $organization = $organizationRepository->find($id);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updateOrganization->execute($organization);
            $this->addFlash('success', 'L\'organisation a été mise à jour.');

            return $this->redirectToRoute('app_admin_organizations_show', [
                'id' => $organization->getId(),
            ]);
        }

        return $this->render('admin/organizations/edit.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/gestion/organisations/{id}/supprimer', name: 'app_admin_organizations_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        OrganizationRepository $organizationRepository,
        DeleteOrganization $deleteOrganization,
    ): Response {
        $organization = $organizationRepository->find($id);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_organization', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_organizations_index');
        }

        if (!$deleteOrganization->execute($organization)) {
            $this->addFlash('error', 'Impossible de supprimer une organisation utilisée.');

            return $this->redirectToRoute('app_admin_organizations_index');
        }

        $this->addFlash('success', 'L\'organisation a été supprimée.');

        return $this->redirectToRoute('app_admin_organizations_index');
    }
}
