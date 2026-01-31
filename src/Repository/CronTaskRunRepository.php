<?php

namespace App\Repository;

use App\Entity\CronTaskRun;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CronTaskRun>
 */
class CronTaskRunRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CronTaskRun::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('run');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('run.command LIKE :search OR run.status LIKE :search OR run.summary LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}
