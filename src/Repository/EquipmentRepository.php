<?php

namespace App\Repository;

use App\Entity\Equipment;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Equipment>
 */
class EquipmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipment::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('e')
            ->join('e.equipmentType', 'et');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('e.label LIKE :search OR et.label LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}
