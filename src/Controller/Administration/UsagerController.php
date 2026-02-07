<?php

namespace App\Controller\Administration;

use App\Entity\User;
use App\Form\UsagerType;
use App\Repository\UserRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use App\UseCase\User\CreateUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/gestion/usagers', name: 'app_admin_usagers_')]
#[IsGranted(new Expression('is_granted("ROLE_APP_MANAGER")'))]
class UsagerController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(
        Request $request,
        UserRepository $userRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'lastname',
            'direction' => 'asc',
            'per_page' => 10,
        ]);

        $qb = $userRepository->createUsagerTableQb($params);
        $pager = $tablePaginator->paginate(
            $qb,
            $params,
            ['firstname', 'lastname', 'email', 'organization', 'isActive', 'updatedAt'],
            'u',
            ['organization' => 'CASE WHEN org.displayName IS NOT NULL AND org.displayName <> \'\' THEN org.displayName ELSE org.legalName END']
        );
        if ('lastname' === $params->sort) {
            $direction = 'desc' === $params->direction ? 'desc' : 'asc';
            $qb->addOrderBy('u.firstname', $direction);
        }
        if ('organization' === $params->sort) {
            $direction = 'desc' === $params->direction ? 'desc' : 'asc';
            $qb->addOrderBy('u.lastname', $direction)
                ->addOrderBy('u.firstname', $direction);
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/usagers/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/usagers/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }

    #[Route('/nouveau', name: 'new')]
    public function new(
        Request $request,
        CreateUser $createUser,
    ): Response {
        $user = new User();
        $form = $this->createForm(UsagerType::class, $user, [
            'require_password' => true,
            'validation_groups' => ['Default', 'password'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $createUser->execute($user, $plainPassword);

            $this->addFlash('success', 'L’usager a été créé.');

            return $this->redirectToRoute('app_admin_usagers_index');
        }

        return $this->render('admin/usagers/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{publicIdentifier}', name: 'show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function show(string $publicIdentifier, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$user instanceof User || $this->isAdminUser($user)) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/usagers/show.html.twig', [
            'user' => $user,
        ]);
    }

    private function isAdminUser(User $user): bool
    {
        $adminRoles = [
            User::ROLE_SUPER_ADMIN,
            User::ROLE_BUSINESS_ADMIN,
            User::ROLE_APP_MANAGER,
            User::ROLE_SUPERVISOR,
        ];

        return [] !== array_intersect($user->getRoles(), $adminRoles);
    }
}
