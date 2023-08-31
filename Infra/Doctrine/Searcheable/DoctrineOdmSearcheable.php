<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Searcheable;

use MongoDB\BSON\Regex;

trait DoctrineOdmSearcheable
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
     * @return void
     * @see https://www.yiiframework.com/doc/guide/2.0/en/rest-filtering-collections#filtering-request
     */
    public function processSearch(InputData $inputData): void
    {
        if (empty($inputData->filters)) {
            return;
        }

        foreach ($inputData->filters as $column => $param) {
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
                        $inputData->builder->field($column)->equals(
                            new Regex("^" . preg_quote($value, '/') . ".*", "i")
                        );
                        continue;
                    }

                    if ($operator === SearchOperator::IN) {
                        $inputData->builder->field($column)->in([$value]);
                        continue;
                    }

                    if ($operator === SearchOperator::BETWEEN) {
                        [$date1, $date2] = explode(',', $value);
                        $date1 .= ' 00:00:00';
                        $date2 .= ' 23:59:59';
                        $inputData->builder->field($column)
                            ->gte($date1)
                            ->lte($date2);
                        continue;
                    }
                }
            }

            if (is_string($param)) {
                $inputData->builder->field($column)->equals($param);
                continue;
            }
        }
    }
}
