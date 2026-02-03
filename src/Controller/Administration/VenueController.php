<?php

namespace App\Controller\Administration;

use App\Entity\Venue;
use App\Form\VenueType;
use App\Repository\CountryRepository;
use App\Repository\VenueRepository;
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
class VenueController extends AbstractController
{
    #[Route('/administration/sites', name: 'app_admin_venues_index')]
    public function index(
        Request $request,
        VenueRepository $venueRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'updatedAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $venueRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['name', 'address.city', 'updatedAt'], 'v');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/venues/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/venues/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/sites/nouveau', name: 'app_admin_venues_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $venue = new Venue();
        $form = $this->createForm(VenueType::class, $venue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($venue);
            $entityManager->flush();

            $this->addFlash('success', 'Le site a été créé avec succès.');

            return $this->redirectToRoute('app_admin_venues_show', [
                'publicIdentifier' => $venue->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/venues/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}', name: 'app_admin_venues_show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function show(string $publicIdentifier, VenueRepository $venueRepository, CountryRepository $countryRepository): Response
    {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $countryLabel = null;
        $countryCode = $venue->getAddress()?->getCountry();
        if ($countryCode) {
            $country = $countryRepository->findOneBy(['code' => $countryCode]);
            $countryLabel = $country?->getLabel();
        }

        return $this->render('admin/venues/show.html.twig', [
            'venue' => $venue,
            'country_label' => $countryLabel,
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/modifier', name: 'app_admin_venues_edit', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function edit(Request $request, string $publicIdentifier, VenueRepository $venueRepository, EntityManagerInterface $entityManager): Response
    {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(VenueType::class, $venue);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Le site a été mis à jour.');

            return $this->redirectToRoute('app_admin_venues_show', [
                'publicIdentifier' => $venue->getPublicIdentifier(),
            ]);
        }

        return $this->render('admin/venues/edit.html.twig', [
            'venue' => $venue,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/sites/{publicIdentifier}/supprimer', name: 'app_admin_venues_delete', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'], methods: ['POST'])]
    public function delete(Request $request, string $publicIdentifier, VenueRepository $venueRepository, EntityManagerInterface $entityManager): Response
    {
        $venue = $venueRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$venue) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_venue', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_venues_index');
        }

        $entityManager->remove($venue);
        $entityManager->flush();
        $this->addFlash('success', 'Le site a été supprimé.');

        return $this->redirectToRoute('app_admin_venues_index');
    }
}
