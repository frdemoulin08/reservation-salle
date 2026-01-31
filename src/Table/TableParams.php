<?php

namespace App\Table;

use Symfony\Component\HttpFoundation\Request;

class TableParams
{
    public function __construct(
        public readonly int $page,
        public readonly int $perPage,
        public readonly string $sort,
        public readonly string $direction,
        public readonly array $filters = [],
    ) {
    }

    public static function fromRequest(Request $request, array $defaults = []): self
    {
        $defaults = array_merge([
            'page' => 1,
            'per_page' => 25,
            'sort' => 'createdAt',
            'direction' => 'desc',
        ], $defaults);

        $page = max(1, (int) $request->query->get('page', $defaults['page']));
        $perPage = max(1, (int) $request->query->get('per_page', $defaults['per_page']));
        $sort = $request->query->has('sort')
            ? (string) $request->query->get('sort', '')
            : (string) $defaults['sort'];

        $rawDirection = $request->query->has('direction')
            ? (string) $request->query->get('direction', '')
            : (string) $defaults['direction'];

        $rawDirection = strtolower($rawDirection);
        $direction = in_array($rawDirection, ['asc', 'desc'], true) ? $rawDirection : '';

        if ('' === $sort) {
            $direction = '';
        }
        $filters = $request->query->all('filter');

        return new self($page, $perPage, $sort, $direction, $filters);
    }

    public function toQueryArray(): array
    {
        $query = [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'sort' => $this->sort,
            'direction' => $this->direction,
        ];

        if (!empty($this->filters)) {
            $query['filter'] = $this->filters;
        }

        return $query;
    }
}
