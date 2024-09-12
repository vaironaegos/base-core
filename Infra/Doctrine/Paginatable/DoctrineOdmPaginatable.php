<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Doctrine\Paginatable;

use Doctrine\ODM\MongoDB\Query\Builder;

trait DoctrineOdmPaginatable
{
    protected array $data;
    protected array $paginationData;

    public function buildPagination(InputData $inputData): void
    {
        $offset = ($inputData->currentPage - 1) * $inputData->perPage;

        /** @var Builder $queryTotal */
        /** @var Builder $queryWithLimit */
        /** @var Builder $queryWithoutLimit */
        $queryTotal = clone $inputData->builder;
        $queryWithLimit = clone $inputData->builder;
        $queryWithoutLimit = clone $inputData->builder;

        if ($inputData->skipPagination) {
            $totalData = $queryTotal->getQuery()->getIterator()->toArray();
            $this->data = array_map(fn ($data) => $data->toSoftArray(), $totalData);
            $this->paginationData = [];
            return;
        }

        $allRecordsCount = $queryTotal->count()->getQuery()->execute();
        $recordsCountFiltered = $queryWithoutLimit->count()->getQuery()->execute();
        $paginationData = $queryWithLimit
            ->skip($offset)
            ->limit($inputData->perPage)
            ->getQuery()
            ->getIterator()
            ->toArray();

        $this->data = array_map(fn ($data) => $data->toSoftArray(), $paginationData);

        $this->paginationData = [
            'current' => $inputData->currentPage,
            'perPage' => $inputData->perPage,
            'pagesTotalCount' => ceil($allRecordsCount / $inputData->perPage),
            'recordsCount' => $recordsCountFiltered,
            'count' => count($paginationData)
        ];
    }
}
