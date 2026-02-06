<?php

namespace App\Repository;

use App\Entity\Organization;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('o');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('o.legalName LIKE :search OR o.displayName LIKE :search OR o.siret LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }

    /**
     * @return Organization[]
     */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('o')
            ->addOrderBy('o.displayName', 'ASC')
            ->addOrderBy('o.legalName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
