<?php

namespace App\Controller\Administration;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use App\UseCase\User\CreateUser;
use App\UseCase\User\UpdateUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/utilisateurs', name: 'app_admin_users_')]
#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
class UserController extends AbstractController
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

        $qb = $userRepository->createTableQb($params);
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
            return $this->render('admin/users/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/users/index.html.twig', [
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
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
            'validation_groups' => ['Default', 'password'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $createUser->execute($user, $plainPassword);

            $this->addFlash('success', 'L’utilisateur a été créé.');

            return $this->redirectToRoute('app_admin_users_index');
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{publicIdentifier}', name: 'show', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function show(string $publicIdentifier, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{publicIdentifier}/edition', name: 'edit', requirements: ['publicIdentifier' => '[0-9a-fA-F\\-]{36}'])]
    public function edit(
        Request $request,
        string $publicIdentifier,
        UserRepository $userRepository,
        UpdateUser $updateUser,
    ): Response {
        $user = $userRepository->findOneBy(['publicIdentifier' => $publicIdentifier]);
        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(UserType::class, $user, [
            'require_password' => false,
            'validation_groups' => static function (FormInterface $form): array {
                $groups = ['Default'];
                $plainPassword = (string) $form->get('plainPassword')->getData();

                if ('' !== $plainPassword) {
                    $groups[] = 'password';
                }

                return $groups;
            },
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            $updateUser->execute($user, $plainPassword);

            $this->addFlash('success', 'L’utilisateur a été mis à jour.');

            return $this->redirectToRoute('app_admin_users_show', ['publicIdentifier' => $user->getPublicIdentifier()]);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
