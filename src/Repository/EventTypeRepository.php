<?php

namespace App\Repository;

use App\Entity\EventType;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventType>
 */
class EventTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventType::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('et');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('et.label LIKE :search OR et.code LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}
