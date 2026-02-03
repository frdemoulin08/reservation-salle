<?php

namespace App\Repository;

use App\Entity\SiteDocumentType;
use App\Entity\Venue;
use App\Table\TableParams;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Venue>
 */
class VenueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Venue::class);
    }

    public function createTableQb(TableParams $params): QueryBuilder
    {
        $qb = $this->createQueryBuilder('v');

        $search = trim((string) ($params->filters['q'] ?? ''));
        if ('' !== $search) {
            $qb
                ->andWhere('v.name LIKE :search OR v.address.city LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        return $qb;
    }

    /**
     * @return Venue[]
     */
    public function findAllWithPublicPhotos(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.documents', 'd', 'WITH', 'd.isPublic = true')
            ->leftJoin('d.documentType', 'dt', 'WITH', 'dt.code = :photoCode')
            ->addSelect('d', 'dt')
            ->setParameter('photoCode', SiteDocumentType::CODE_PHOTO)
            ->orderBy('v.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneWithPublicPhotos(string $publicIdentifier): ?Venue
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.documents', 'd', 'WITH', 'd.isPublic = true')
            ->leftJoin('d.documentType', 'dt', 'WITH', 'dt.code = :photoCode')
            ->addSelect('d', 'dt')
            ->andWhere('v.publicIdentifier = :publicIdentifier')
            ->setParameter('publicIdentifier', $publicIdentifier)
            ->setParameter('photoCode', SiteDocumentType::CODE_PHOTO)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
