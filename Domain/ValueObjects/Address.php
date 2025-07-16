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

    public function value(): string
    {
        return json_encode([
            'zipCode' => $this->zipCode,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'number' => $this->number,
            'street' => $this->street,
            'complement' => $this->complement,
        ]);
    }

    public function toArray(): array
    {
        return [
            'zipCode' => $this->zipCode,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'number' => $this->number,
            'street' => $this->street,
            'complement' => $this->complement,
        ];
    }
}
