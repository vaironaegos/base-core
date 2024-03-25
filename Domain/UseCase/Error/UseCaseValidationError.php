<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\UseCase\Error;

final readonly class UseCaseValidationError implements UseCaseError
{
    public function __construct(
        public string $field,
        public array $details = []
    ) {
    }

    public function output(): array
    {
        return [
            'field' => $this->field,
            'details' => $this->details
        ];
    }
}
