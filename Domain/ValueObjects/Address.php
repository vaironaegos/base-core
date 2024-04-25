<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\ValueObjects;

use Astrotech\Core\Base\Domain\ValueObjectBase;

final class Address extends ValueObjectBase
{
    public function __construct(
        public readonly string $zipCode,
        public readonly string $neighborhood,
        public readonly string $city,
        public readonly string $state,
        public readonly string $number,
        public readonly string $street,
        public readonly ?string $complement = null,
    ) {
    }

    public function value(): string|int|float|bool
    {
        return true;
    }
}
