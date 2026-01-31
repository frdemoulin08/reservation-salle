<?php

namespace App\Repository;

use App\Entity\AuthenticationLog;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthenticationLog>
 */
class AuthenticationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthenticationLog::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('log')
            ->leftJoin('log.user', 'u')
            ->addSelect('u');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere(
                    'log.identifier LIKE :search
                    OR log.eventType LIKE :search
                    OR log.ipAddress LIKE :search
                    OR log.userAgent LIKE :search
                    OR log.failureReason LIKE :search
                    OR u.firstname LIKE :search
                    OR u.lastname LIKE :search
                    OR u.email LIKE :search'
                )
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }

    public function purgeOlderThan(\DateTimeImmutable $cutoff): int
    {
        return $this->createQueryBuilder('log')
            ->delete()
            ->where('log.occurredAt < :cutoff')
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->execute();
    }
}
