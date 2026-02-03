<?php

namespace App\Controller\Administration;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;
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
class CountryController extends AbstractController
{
    #[Route('/administration/parametrage/pays', name: 'app_admin_countries_index')]
    public function index(
        Request $request,
        CountryRepository $countryRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'label',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $countryRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['label', 'code', 'dialingCode', 'isActive'], 'c');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/countries/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/countries/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/parametrage/pays/nouveau', name: 'app_admin_countries_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $country = new Country();
        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($country);
            $entityManager->flush();

            $this->addFlash('success', 'Le pays a été créé avec succès.');

            return $this->redirectToRoute('app_admin_countries_show', [
                'publicIdentifier' => $country->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/countries/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/pays/{publicIdentifier}', name: 'app_admin_countries_show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function show(string $publicIdentifier, CountryRepository $countryRepository): Response
    {
        $country = $countryRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$country) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/countries/show.html.twig', [
            'country' => $country,
        ]);
    }

    #[Route('/administration/parametrage/pays/{publicIdentifier}/modifier', name: 'app_admin_countries_edit', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function edit(Request $request, string $publicIdentifier, CountryRepository $countryRepository, EntityManagerInterface $entityManager): Response
    {
        $country = $countryRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$country) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(CountryType::class, $country);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le pays a été mis à jour.');

            return $this->redirectToRoute('app_admin_countries_show', [
                'publicIdentifier' => $country->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/countries/edit.html.twig', [
            'country' => $country,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/parametrage/pays/{publicIdentifier}/supprimer', name: 'app_admin_countries_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['POST'])]
    public function delete(Request $request, string $publicIdentifier, CountryRepository $countryRepository, EntityManagerInterface $entityManager): Response
    {
        $country = $countryRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$country) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_country', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_countries_index');
        }

        $entityManager->remove($country);
        $entityManager->flush();
        $this->addFlash('success', 'Le pays a été supprimé.');

        return $this->redirectToRoute('app_admin_countries_index');
    }
}
