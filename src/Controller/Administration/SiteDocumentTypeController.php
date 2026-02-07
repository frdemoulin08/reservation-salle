<?php

namespace App\Controller\Administration;

use App\Entity\SiteDocumentType;
use App\Form\SiteDocumentTypeType;
use App\Repository\SiteDocumentTypeRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use App\UseCase\SiteDocumentType\CreateSiteDocumentType;
use App\UseCase\SiteDocumentType\DeleteSiteDocumentType;
use App\UseCase\SiteDocumentType\UpdateSiteDocumentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN")'))]
class SiteDocumentTypeController extends AbstractController
{
    #[Route('/administration/parametrage/types-documents', name: 'app_admin_site_document_types_index')]
    public function index(
        Request $request,
        SiteDocumentTypeRepository $siteDocumentTypeRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'position',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $siteDocumentTypeRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label', 'code', 'position', 'isPublic', 'isActive', 'isRequired', 'isMultipleAllowed'], 'sdt');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/site_document_types/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/site_document_types/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/types-documents/nouveau', name: 'app_admin_site_document_types_new')]
    public function new(Request $request, CreateSiteDocumentType $createSiteDocumentType): Response
    {
        $documentType = new SiteDocumentType();
        $form = $this->createForm(SiteDocumentTypeType::class, $documentType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $createSiteDocumentType->execute($documentType);

            $this->addFlash('success', 'Le type de document a été créé avec succès.');

            return $this->redirectToRoute('app_admin_site_document_types_index');
        }

        return $this->render('admin/site_document_types/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-documents/{id}', name: 'app_admin_site_document_types_show', requirements: ['id' => '\\d+'])]
    public function show(int $id, SiteDocumentTypeRepository $siteDocumentTypeRepository): Response
    {
        $documentType = $siteDocumentTypeRepository->find($id);
        if (!$documentType instanceof SiteDocumentType) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/site_document_types/show.html.twig', [
            'document_type' => $documentType,
        ]);
    }

    #[Route('/administration/parametrage/types-documents/{id}/modifier', name: 'app_admin_site_document_types_edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        int $id,
        SiteDocumentTypeRepository $siteDocumentTypeRepository,
        UpdateSiteDocumentType $updateSiteDocumentType,
    ): Response {
        $documentType = $siteDocumentTypeRepository->find($id);
        if (!$documentType instanceof SiteDocumentType) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(SiteDocumentTypeType::class, $documentType, [
            'is_edit' => true,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updateSiteDocumentType->execute($documentType);

            $this->addFlash('success', 'Le type de document a été mis à jour.');

            return $this->redirectToRoute('app_admin_site_document_types_index');
        }

        return $this->render('admin/site_document_types/edit.html.twig', [
            'document_type' => $documentType,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/types-documents/{id}/supprimer', name: 'app_admin_site_document_types_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(
        Request $request,
        int $id,
        SiteDocumentTypeRepository $siteDocumentTypeRepository,
        DeleteSiteDocumentType $deleteSiteDocumentType,
    ): Response {
        $documentType = $siteDocumentTypeRepository->find($id);
        if (!$documentType instanceof SiteDocumentType) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_site_document_type', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_site_document_types_index');
        }

        if (!$deleteSiteDocumentType->execute($documentType)) {
            $this->addFlash('error', 'Impossible de supprimer un type de document déjà utilisé.');

            return $this->redirectToRoute('app_admin_site_document_types_index');
        }

        $this->addFlash('success', 'Le type de document a été supprimé.');

        return $this->redirectToRoute('app_admin_site_document_types_index');
    }
}
