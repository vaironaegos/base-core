<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Paginatable;

use Doctrine\ODM\MongoDB\Query\Builder;

trait DoctrineOdmPaginatable
{
    protected array $data;
    protected array $paginationData;

    public function buildPagination(InputData $inputData): void
    {
        $offset = ($inputData->currentPage - 1) * $inputData->perPage;

        /** @var Builder $queryTotal */
        $queryTotal = $inputData->builder;
        $totalData = $queryTotal->getQuery()->getIterator()->toArray();

        if ($inputData->skipPagination) {
            $this->data = array_map(fn ($data) => $data->toSoftArray(), $totalData);
            $this->paginationData = [];
            return;
        }

        /** @var Builder $query */
        $query = $inputData->builder
            ->skip($offset)
            ->limit($inputData->perPage);

        $paginationData = $query->getQuery()->getIterator()->toArray();

        $this->data = array_map(fn ($data) => $data->toSoftArray(), $paginationData);

        $this->paginationData = [
            'current' => $inputData->currentPage,
            'perPage' => $inputData->perPage,
            'pagesTotalCount' => ceil(count($totalData) / $inputData->perPage),
            'recordsCount' => count($totalData),
            'count' => count($paginationData)
        ];
    }
}
