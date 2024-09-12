<?php

namespace Astrotech\Core\Base\Infra\Doctrine\Paginatable;

trait MongoDbPaginatable
{
    protected array $paginationData;

    public function buildPagination(InputData $inputData): array
    {
        $offset = ($inputData->currentPage - 1) * $inputData->perPage;
        $allDocumentsCount = count($this->collection->find()->toArray());

        $this->paginationData = [
            'current' => $inputData->currentPage,
            'perPage' => $inputData->perPage,
            'pagesTotalCount' => ceil($allDocumentsCount / $inputData->perPage),
            'recordsCount' => $allDocumentsCount,
        ];

        return ['limit' => $inputData->perPage, 'skip' => $offset];
    }
}
