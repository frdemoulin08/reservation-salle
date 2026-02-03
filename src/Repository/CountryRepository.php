<?php

namespace App\Repository;

use App\Entity\Country;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Country>
 */
class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    /**
     * @return Country[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('country')
            ->andWhere('country.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('country.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('c');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('c.label LIKE :search OR c.code LIKE :search OR c.dialingCode LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}
