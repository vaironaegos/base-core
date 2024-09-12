<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Doctrine\Searcheable;

use MongoDB\BSON\Regex;

trait DoctrineOdmSearcheable
{
    private InputData $inputData;

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
     * @param InputData $inputData
     * @return void
     * @see https://www.yiiframework.com/doc/guide/2.0/en/rest-filtering-collections#filtering-request
     */
    public function processSearch(InputData $inputData): void
    {
        $this->inputData = $inputData;

        if (empty($this->inputData->filters)) {
            return;
        }

        foreach ($this->inputData->filters as $column => $param) {
            if (empty($param)) {
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
                        $this->applyLikeOperator($column, $value);
                        continue;
                    }

                    if ($operator === SearchOperator::IN) {
                        $this->applyInOperator($column, explode(',', $value));
                        continue;
                    }

                    if ($operator === SearchOperator::BETWEEN) {
                        [$startDate, $endDate] = explode(',', $value);
                        $this->applyBetweenOperator($column, $startDate, $endDate);
                    }
                }
            }

            if (is_string($param)) {
                $this->applyEqualsOperator($column, $param);
            }
        }
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
