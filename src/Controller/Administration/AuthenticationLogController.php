<?php

namespace App\Controller\Administration;

use App\Repository\AuthenticationLogRepository;
use App\Table\TablePaginator;
use App\Table\TableParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/administration', name: 'app_admin_')]
#[IsGranted(new Expression('is_granted("ROLE_SUPER_ADMIN") or is_granted("ROLE_BUSINESS_ADMIN") or is_granted("ROLE_APP_MANAGER")'))]
class AuthenticationLogController extends AbstractController
{
    #[Route('/authentication-logs', name: 'authentication_logs_index')]
    public function index(
        Request $request,
        AuthenticationLogRepository $authenticationLogRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'occurredAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $authenticationLogRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['occurredAt', 'identifier', 'eventType', 'ipAddress'], 'log');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/authentication_logs/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/authentication_logs/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }
}
