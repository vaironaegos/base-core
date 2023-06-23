<?php

declare(strict_types=1);

if (!function_exists('force2Decimals')) {
    function force2Decimals(float $number): float
    {
        return (float)floor($number * 100) / 100;
    }
}

if (!function_exists('force4Decimals')) {
    function force4Decimals(float $number): float
    {
        return (float)number_format($number, 4, '.', '');
    }
}

if (!function_exists('force8Decimals')) {
    function force8Decimals(float $number): float
    {
        return (float)number_format($number, 8, '.', '');
    }
}

if (!function_exists('randomFloat')) {
    function randomFloat(float $min, float $max): float
    {
        return ($min + lcg_value() * (abs($max - $min)));
    }
}
