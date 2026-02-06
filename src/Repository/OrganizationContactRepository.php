<?php

namespace App\Repository;

use App\Entity\OrganizationContact;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrganizationContact>
 */
class OrganizationContactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganizationContact::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('oc');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('oc.firstName LIKE :search OR oc.lastName LIKE :search OR oc.email LIKE :search OR oc.role LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}
