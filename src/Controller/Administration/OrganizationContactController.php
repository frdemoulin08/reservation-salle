<?php

namespace App\Controller\Administration;

use App\Entity\Organization;
use App\Entity\OrganizationContact;
use App\Entity\Reservation;
use App\Form\OrganizationContactType;
use App\Repository\OrganizationContactRepository;
use App\Repository\OrganizationRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_APP_MANAGER") or is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN")'))]
class OrganizationContactController extends AbstractController
{
    #[Route('/administration/gestion/organisations/{organizationId}/contacts', name: 'app_admin_organization_contacts_index', requirements: ['organizationId' => '\\d+'])]
    public function index(
        int $organizationId,
        Request $request,
        OrganizationRepository $organizationRepository,
        OrganizationContactRepository $contactRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $organization = $organizationRepository->find($organizationId);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        $params = TableParams::fromRequest($request, [
            'sort' => 'lastName',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $contactRepository->createTableQb($params);
        $qb->andWhere('oc.organization = :organization')
            ->setParameter('organization', $organization);

        $pager = $tablePaginator->paginate($qb, $params, ['firstName', 'lastName', 'email', 'role'], 'oc');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/organization_contacts/_table.html.twig', [
                'organization' => $organization,
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/organization_contacts/index.html.twig', [
            'organization' => $organization,
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/administration/gestion/organisations/{organizationId}/contacts/nouveau', name: 'app_admin_organization_contacts_new', requirements: ['organizationId' => '\\d+'])]
    public function new(
        int $organizationId,
        Request $request,
        OrganizationRepository $organizationRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $organization = $organizationRepository->find($organizationId);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        $contact = new OrganizationContact();
        $contact->setOrganization($organization);

        $form = $this->createForm(OrganizationContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contact);
            $entityManager->flush();

            $this->addFlash('success', 'Le contact a été créé avec succès.');

            return $this->redirectToRoute('app_admin_organization_contacts_index', [
                'organizationId' => $organization->getId(),
            ]);
        }

        return $this->render('admin/organization_contacts/new.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/gestion/organisations/{organizationId}/contacts/{id}', name: 'app_admin_organization_contacts_show', requirements: ['organizationId' => '\\d+', 'id' => '\\d+'])]
    public function show(
        int $organizationId,
        int $id,
        OrganizationRepository $organizationRepository,
        OrganizationContactRepository $contactRepository,
    ): Response {
        $organization = $organizationRepository->find($organizationId);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        $contact = $contactRepository->find($id);
        if (!$contact instanceof OrganizationContact || $contact->getOrganization()?->getId() !== $organization->getId()) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/organization_contacts/show.html.twig', [
            'organization' => $organization,
            'contact' => $contact,
        ]);
    }

    #[Route('/administration/gestion/organisations/{organizationId}/contacts/{id}/modifier', name: 'app_admin_organization_contacts_edit', requirements: ['organizationId' => '\\d+', 'id' => '\\d+'])]
    public function edit(
        int $organizationId,
        int $id,
        Request $request,
        OrganizationRepository $organizationRepository,
        OrganizationContactRepository $contactRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $organization = $organizationRepository->find($organizationId);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        $contact = $contactRepository->find($id);
        if (!$contact instanceof OrganizationContact || $contact->getOrganization()?->getId() !== $organization->getId()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(OrganizationContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Le contact a été mis à jour.');

            return $this->redirectToRoute('app_admin_organization_contacts_show', [
                'organizationId' => $organization->getId(),
                'id' => $contact->getId(),
            ]);
        }

        return $this->render('admin/organization_contacts/edit.html.twig', [
            'organization' => $organization,
            'contact' => $contact,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/administration/gestion/organisations/{organizationId}/contacts/{id}/supprimer', name: 'app_admin_organization_contacts_delete', requirements: ['organizationId' => '\\d+', 'id' => '\\d+'], methods: ['POST'])]
    public function delete(
        int $organizationId,
        int $id,
        Request $request,
        OrganizationRepository $organizationRepository,
        OrganizationContactRepository $contactRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $organization = $organizationRepository->find($organizationId);
        if (!$organization instanceof Organization) {
            throw $this->createNotFoundException();
        }

        $contact = $contactRepository->find($id);
        if (!$contact instanceof OrganizationContact || $contact->getOrganization()?->getId() !== $organization->getId()) {
            throw $this->createNotFoundException();
        }

        if (!$this->isCsrfTokenValid('delete_organization_contact', (string) $request->request->get('_token'))) {
            return $this->redirectToRoute('app_admin_organization_contacts_index', [
                'organizationId' => $organization->getId(),
            ]);
        }

        $reservationsCount = $entityManager->getRepository(Reservation::class)->count([
            'organizationContact' => $contact,
        ]);
        if ($reservationsCount > 0) {
            $this->addFlash('error', 'Impossible de supprimer un contact déjà utilisé dans des réservations.');

            return $this->redirectToRoute('app_admin_organization_contacts_index', [
                'organizationId' => $organization->getId(),
            ]);
        }

        $entityManager->remove($contact);
        $entityManager->flush();
        $this->addFlash('success', 'Le contact a été supprimé.');

        return $this->redirectToRoute('app_admin_organization_contacts_index', [
            'organizationId' => $organization->getId(),
        ]);
    }
}
