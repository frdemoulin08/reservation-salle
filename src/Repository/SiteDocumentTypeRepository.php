<?php

namespace App\Repository;

use App\Entity\SiteDocumentType;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SiteDocumentType>
 */
class SiteDocumentTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SiteDocumentType::class);
    }

    /**
     * @return SiteDocumentType[]
     */
    public function findActiveOrdered(): array
    {
        return $this->createQueryBuilder('sdt')
            ->andWhere('sdt.isActive = true')
            ->orderBy('sdt.position', 'ASC')
            ->addOrderBy('sdt.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByCode(string $code): ?SiteDocumentType
    {
        return $this->findOneBy(['code' => mb_strtoupper($code)]);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('sdt');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('sdt.label LIKE :search OR sdt.code LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }
}
