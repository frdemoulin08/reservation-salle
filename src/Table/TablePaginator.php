<?php

namespace App\Table;

use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TablePaginator
{
    /**
     * @param string[] $allowedSorts
     * @param array<string, string> $sortMap
     */
    public function paginate(
        QueryBuilder $qb,
        TableParams $params,
        array $allowedSorts,
        string $alias = 'e',
        array $sortMap = [],
    ): Pagerfanta {
        if ('' !== $params->sort) {
            $sort = in_array($params->sort, $allowedSorts, true)
                ? $params->sort
                : $allowedSorts[0];

            $direction = 'asc' === $params->direction ? 'asc' : 'desc';
            if (array_key_exists($sort, $sortMap)) {
                $qb->orderBy($sortMap[$sort], $direction);
            } else {
                $qb->orderBy(sprintf('%s.%s', $alias, $sort), $direction);
            }
        }

        $pager = new Pagerfanta(new QueryAdapter($qb));
        $pager->setMaxPerPage($params->perPage);
        $pager->setCurrentPage($params->page);

        return $pager;
    }
}
