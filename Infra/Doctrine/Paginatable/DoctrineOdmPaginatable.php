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
        $collection = $queryTotal->getQuery()->getClass();
        $recordsCount = $queryTotal->getQuery()->getDocumentManager()
            ->getDocumentCollection($collection->getName())->countDocuments();

        if ($inputData->skipPagination) {
            $totalData = $queryTotal->getQuery()->getIterator()->toArray();
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
            'pagesTotalCount' => ceil($recordsCount / $inputData->perPage),
            'recordsCount' => $recordsCount,
            'count' => count($paginationData)
        ];
    }
}
