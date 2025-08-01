<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\ValueObjects;

use Astrotech\Core\Base\Domain\ValueObjectBase;

final class Phone extends ValueObjectBase
{
    private string $phone;

    public function __construct(string $phone)
    {
        $phoneSanitized = preg_replace('/[^0-9]/', '', $phone);
        $this->phone = $phoneSanitized;
    }

    public function value(): string
    {
        return $this->phone;
    }
}
