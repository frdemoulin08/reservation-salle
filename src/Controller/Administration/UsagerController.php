<?php

namespace App\Controller\Administration;

use App\Entity\User;
use App\Form\UsagerType;
use App\Repository\UserRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration/gestion/usagers', name: 'app_admin_usagers_')]
#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN") or is_granted("ROLE_APP_MANAGER")'))]
class UsagerController extends AbstractController
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

        $qb = $userRepository->createUsagerTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['firstname', 'lastname', 'email', 'isActive', 'updatedAt'], 'u');

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
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $user = new User();
        $form = $this->createForm(UsagerType::class, $user, [
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

            $this->addFlash('success', 'L’usager a été créé.');

            return $this->redirectToRoute('app_admin_usagers_index');
        }

        return $this->render('admin/usagers/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
