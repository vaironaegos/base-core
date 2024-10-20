<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase\Error;

final class UseCaseValidationError implements UseCaseError
{
    public function __construct(
        public readonly string $field,
        public readonly mixed $providedValue = null,
        public readonly array $details = [],
    ) {
    }

    public function output(): array
    {
        return [
            'field' => $this->field,
            'value' => $this->providedValue,
            'details' => $this->details
        ];
    }
}
