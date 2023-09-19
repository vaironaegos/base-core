<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Searcheable;

use MongoDB\BSON\Regex;

trait MongoDbSearchable
{
    /**
     * Operators to be used in request query string:
     * - eq (=)
     * - neq (!= ou <>)
     * - in (IN)
     * - nin (NOT IN)
     * - like (LIKE)
     * - lt (<)
     * - gt (>)
     * - lte (<=)
     * - gte (>=)
     * - btw (BETWEEN)
     *
     * @see https://www.yiiframework.com/doc/guide/2.0/en/rest-filtering-collections#filtering-request
     */
    public function processSearch(array $filters = []): array
    {
        if (empty($filters)) {
            return [];
        }

        $findFilters = [];

        foreach ($filters as $column => $param) {
            if ($param === '') {
                continue;
            }

            if (is_array($param)) {
                foreach ($param as $operator => $value) {
                    $value = trim($value);
                    $operator = SearchOperator::tryFrom($operator);

                    if (empty($value)) {
                        continue;
                    }

                    if ($operator === SearchOperator::LIKE) {
                        $findFilters[$column] = new Regex("^" . preg_quote($value, '/') . ".*", "i");
                        continue;
                    }

                    if ($operator === SearchOperator::IN) {
                        $findFilters[$column] = ['$in' => explode(',', $value)];
                        continue;
                    }

                    if ($operator === SearchOperator::EQUAL) {
                        $findFilters[$column] = ['$eq' => $value];
                        continue;
                    }

                    if ($operator === SearchOperator::BETWEEN) {
                        [$startDate, $endDate] = explode(',', $value);
                        $findFilters[$column] = ['$gte' => $startDate, '$lte' => $endDate];
                    }
                }
            }

            if (is_string($param)) {
                $findFilters[$column] = match ($param) {
                    !is_numeric($param) => $param,
                    default => intval($param)
                };
            }
        }

        return $findFilters;
    }

    private function applyLikeOperator(string $column, mixed $value): void
    {
        $this->inputData->builder->field($column)->equals(new Regex("^" . preg_quote($value, '/') . ".*", "i"));
    }

    private function applyInOperator(string $column, array $values): void
    {
        $this->inputData->builder->field($column)->in($values);
    }

    private function applyBetweenOperator(string $column, string $startDate, string $endDate): void
    {
        $this->inputData
            ->builder
            ->field($column)
            ->gte($startDate . ' 00:00:00')
            ->lte($endDate . ' 23:59:59');
    }

    private function applyEqualsOperator(string $column, mixed $value): void
    {
        $this->inputData->builder->field($column)->equals($value);
    }
}
