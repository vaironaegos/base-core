<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Sorteable;

use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\BSON\Regex;

trait DoctrineOdmSorteable
{
    public function processSort(InputData $inputData): void
    {
        if (is_null($inputData->params)) {
            return;
        }

        $params = $inputData->params;

        if (str_contains($inputData->params, ',')) {
            $params = explode(',', $inputData->params);
        }

        if (is_array($params)) {
            foreach ($params as $column) {
                if (str_contains($column, '-')) {
                    $key = explode('-', $column);
                    $inputData->builder->sort($key[1], 'desc');
                    continue;
                }

                $inputData->builder->sort($column, 'asc');
            }
            return;
        }

        if (str_contains($params, '-')) {
            $key = explode('-', $params);
            $inputData->builder->sort($key[1], 'desc');
            return;
        }

        $inputData->builder->sort($params, 'asc');
    }
}
