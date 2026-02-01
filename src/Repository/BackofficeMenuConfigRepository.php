<?php

namespace App\Repository;

use App\Entity\BackofficeMenuConfig;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BackofficeMenuConfig>
 */
class BackofficeMenuConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BackofficeMenuConfig::class);
    }

    public function findActive(): ?BackofficeMenuConfig
    {
        return $this->createQueryBuilder('config')
            ->andWhere('config.isActive = true')
            ->orderBy('config.updatedAt', 'DESC')
            ->addOrderBy('config.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
