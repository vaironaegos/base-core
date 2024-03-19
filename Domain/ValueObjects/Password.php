<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\ValueObjects;

use Astrotech\Core\Base\Domain\ValueObjectBase;

final class Password extends ValueObjectBase
{
    private string $value;

    public function __construct(string $password)
    {
        if (!str_contains($password, 'argon2i')) {
            $this->value = password_hash($password, PASSWORD_ARGON2I);
            return;
        }

        $this->value = $password;
    }

    public function value(): string|int|float|bool
    {
        return $this->value;
    }
}
