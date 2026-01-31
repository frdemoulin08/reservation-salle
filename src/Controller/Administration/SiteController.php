<?php

namespace App\Controller\Administration;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
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
class SiteController extends AbstractController
{
    #[Route('/administration/sites', name: 'app_admin_sites_index')]
    public function index(
        Request $request,
        SiteRepository $siteRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'updatedAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $siteRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['name', 'city', 'capacity', 'status', 'updatedAt'], 's');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/sites/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/sites/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/sites/new', name: 'app_admin_sites_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($site);
            $entityManager->flush();

            $this->addFlash('success', 'Le site a été créé avec succès.');

            return $this->redirectToRoute('app_admin_sites_show', ['id' => $site->getId()]);
        }

        return $this->render('admin/sites/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/sites/{id}', name: 'app_admin_sites_show', requirements: ['id' => '\\d+'])]
    public function show(Site $site): Response
    {
        return $this->render('admin/sites/show.html.twig', [
            'site' => $site,
        ]);
    }

    #[Route('/administration/sites/{id}/edit', name: 'app_admin_sites_edit', requirements: ['id' => '\\d+'])]
    public function edit(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le site a été mis à jour.');

            return $this->redirectToRoute('app_admin_sites_show', ['id' => $site->getId()]);
        }

        return $this->render('admin/sites/edit.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/sites/{id}/delete', name: 'app_admin_sites_delete', requirements: ['id' => '\\d+'], methods: ['POST'])]
    public function delete(Request $request, Site $site, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_site', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_sites_index');
        }

        $entityManager->remove($site);
        $entityManager->flush();
        $this->addFlash('success', 'Le site a été supprimé.');

        return $this->redirectToRoute('app_admin_sites_index');
    }
}
