<?php

declare(strict_types=1);

if (!function_exists('isDateIso8601')) {
    function isDateIso8601(string $date): bool
    {
        if (empty($date)) {
            return false;
        }

        return preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date) > 0;
    }
}

if (!function_exists('isDateTimeIso8601')) {
    function isDateTimeIso8601(string $dateTime): bool
    {
        if (empty($dateTime)) {
            return false;
        }

        return preg_match("/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/", $dateTime) > 0;
    }
}

if (!function_exists('isDateTimeIso')) {
    function isDateTimeIso(string $dateTime): bool
    {
        if (empty($dateTime)) {
            return false;
        }

        return preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|([+\-])\d{2}(:?\d{2})?)$/',
            $dateTime
        ) > 0;
    }
}

if (!function_exists('convertDateToBr')) {
    function convertDateToBr(): string
    {
        $months = [
            'January' => 'Janeiro',
            'February' => 'Fevereiro',
            'March' => 'Março',
            'April' => 'Abril',
            'May' => 'Maio',
            'June' => 'Junho',
            'July' => 'Julho',
            'August' => 'Agosto',
            'September' => 'Setembro',
            'October' => 'Outubro',
            'November' => 'Novembro',
            'December' => 'Dezembro'
        ];

        $now = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
        $now = $now->format('d F Y');
        $date = explode(' ', $now);
        $month = $date[1];
        $month = $months[$month];

        return "{$date[0]} de {$month} de {$date[2]}";
    }
}
