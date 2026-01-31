<?php

namespace App\Controller\Administration;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/utilisateurs', name: 'app_admin_users_')]
#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN") or is_granted("ROLE_APP_MANAGER")'))]
class UserController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(
        Request $request,
        UserRepository $userRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'updatedAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $userRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['firstname', 'lastname', 'email', 'isActive', 'updatedAt'], 'u');

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
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user, [
            'require_password' => true,
            'validation_groups' => ['Default', 'password'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = (string) $form->get('plainPassword')->getData();
            if ('' !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'L’utilisateur a été créé.');

            return $this->redirectToRoute('app_admin_users_index');
        }

        return $this->render('admin/users/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\\d+'])]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edition', name: 'edit', requirements: ['id' => '\\d+'])]
    public function edit(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
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
            if ('' !== $plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', 'L’utilisateur a été mis à jour.');

            return $this->redirectToRoute('app_admin_users_show', ['id' => $user->getId()]);
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
