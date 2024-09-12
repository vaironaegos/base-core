<?php

namespace Astrotech\Core\Base\Infra\Doctrine\Sorteable;

trait MongoDbSortable
{
    public function processSort(array $sortParams = []): array
    {
        if (empty($sortParams)) {
            return [];
        }

        $findSort = [];

        foreach ($sortParams as $column) {
            if (str_contains($column, '-')) {
                [,$key] = explode('-', $column);
                $findSort[$key] = -1;
                continue;
            }

            $findSort[$column] = 1;
        }

        return ['sort' => $findSort];
    }
}
