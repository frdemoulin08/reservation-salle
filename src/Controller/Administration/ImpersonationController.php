<?php

namespace App\Controller\Administration;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN")'))]
class ImpersonationController extends AbstractController
{
    #[Route('/administration/impersonation/options', name: 'app_admin_impersonation_options')]
    public function options(UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->createQueryBuilder('u')
            ->orderBy('u.lastname', 'ASC')
            ->addOrderBy('u.firstname', 'ASC')
            ->getQuery()
            ->getResult();

        $options = [];
        foreach ($users as $user) {
            $lastname = mb_strtoupper($user->getLastname());
            $firstname = mb_convert_case(mb_strtolower($user->getFirstname()), MB_CASE_TITLE, 'UTF-8');
            $label = sprintf('%s %s', $lastname, $firstname);
            $options[] = [
                'label' => $label,
                'value' => $user->getEmail(),
            ];
        }

        $selected = (string) $request->query->get('_switch_user', '');

        return $this->render('admin/_impersonation_options.html.twig', [
            'options' => $options,
            'selected' => $selected,
        ]);
    }
}
