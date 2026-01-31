<?php

namespace App\Controller\Administration;

use App\Repository\CronTaskRunRepository;
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
class CronTaskRunController extends AbstractController
{
    #[Route('/journal-taches', name: 'cron_tasks_index')]
    public function index(
        Request $request,
        CronTaskRunRepository $cronTaskRunRepository,
        TablePaginator $tablePaginator,
    ): Response {
        $params = TableParams::fromRequest($request, [
            'sort' => 'startedAt',
            'direction' => 'desc',
            'per_page' => 10,
        ]);

        $qb = $cronTaskRunRepository->createTableQb($params);
        $pager = $tablePaginator->paginate($qb, $params, ['startedAt', 'command', 'status', 'durationMs', 'exitCode'], 'run');

        if ($request->isXmlHttpRequest()) {
            return $this->render('admin/cron_tasks/_table.html.twig', [
                'pager' => $pager,
                'params' => $params,
            ]);
        }

        return $this->render('admin/cron_tasks/index.html.twig', [
            'pager' => $pager,
            'params' => $params,
        ]);
    }
}
