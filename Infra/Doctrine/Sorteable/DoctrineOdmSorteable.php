<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Doctrine\Sorteable;

trait DoctrineOdmSorteable
{
    public function processSort(InputData $inputData): void
    {
        if (is_null($inputData->params)) {
            return;
        }

        foreach ($inputData->params as $column) {
            if (str_contains($column, '-')) {
                $key = explode('-', $column);
                $inputData->builder->sort($key[1], 'desc');
                continue;
            }

            $inputData->builder->sort($column, 'asc');
        }
    }
}
