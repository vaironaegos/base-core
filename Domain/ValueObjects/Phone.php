<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\ValueObjects;

use Astrotech\ApiBase\Domain\ValueObjectBase;

final class Phone extends ValueObjectBase
{
    private string $phone;

    public function __construct(string $phone, string $country = 'br')
    {
        $phoneSanitized = preg_replace('/[^0-9]/', '', $phone);
        $this->phone = $phoneSanitized;
    }

    public function value(): string|int|float|bool
    {
        return $this->phone;
    }
}
