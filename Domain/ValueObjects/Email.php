<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\ValueObjects;

use Astrotech\Core\Base\Domain\ValueObjectBase;
use DomainException;

final class Email extends ValueObjectBase
{
    private string $value = '';
    private bool $isValid = false;

    public function __construct(string $email, bool $strict = true)
    {
        $email = trim(strtolower($email));

        if (empty($email)) {
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ($strict) {
                throw new DomainException('Invalid E-mail "' . $email . "'");
            }

            return;
        }

        $this->value = $email;
        $this->isValid = true;
    }

    public function value(): string|int|float|bool
    {
        return $this->value;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }
}
