<?php

declare(strict_types=1);

if (!function_exists('arraySort')) {
    function arraySort($array, $on, $order = SORT_ASC)
    {
        $new_array = [];
        $sortable_array = [];

        if (count($array) > 0) {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    foreach ($v as $k2 => $v2) {
                        if ($k2 == $on) {
                            $sortable_array[$k] = $v2;
                        }
                    }
                } else {
                    $sortable_array[$k] = $v;
                }
            }

            switch ($order) {
                case SORT_ASC:
                    asort($sortable_array);
                    break;
                case SORT_DESC:
                    arsort($sortable_array);
                    break;
            }

            foreach ($sortable_array as $k => $v) {
                $new_array[$k] = $array[$k];
            }
        }

        return $new_array;
    }
}

if (!function_exists('sumMultidimensionalArray')) {
    function sumMultidimensionalArray(array $array, array $keys): float
    {
        return array_reduce($array, function ($acc, $item) use ($keys) {
            return $acc + array_sum(array_intersect_key($item, array_flip($keys)));
        }, 0);
    }
}

if (!function_exists('arraysAreEqual')) {
    function arraysAreEqual(array $array1, array $array2, array $ignoreKeys = [])
    {
        if (count($array1) !== count($array2)) {
            return false;
        }

        foreach ($array1 as $key => $value) {
            if (in_array($key, $ignoreKeys)) {
                continue;
            }

            if (!array_key_exists($key, $array2)) {
                return false;
            }

            if (is_array($value)) {
                if (!arraysAreEqual($value, $array2[$key], $ignoreKeys)) {
                    return false;
                }
            } else {
                if ($value !== $array2[$key]) {
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('unsetKeyInAssociativeArray')) {
    function unsetKeyInAssociativeArray(&$array, $keyToUnset)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                unsetKeyInAssociativeArray($value, $keyToUnset);
            } elseif ($key === $keyToUnset) {
                unset($array[$key]);
            }
        }
    }
}
