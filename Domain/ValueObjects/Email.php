<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\ValueObjects;

use Astrotech\ApiBase\Domain\ValueObjectBase;
use DomainException;

final class Email extends ValueObjectBase
{
    private string $value = '';

    public function __construct(string $email)
    {
        $email = trim(strtolower($email));

        if (empty($email)) {
            return null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Invalid E-mail "' . $email . "'");
        }

        $this->value = $email;
    }

    public function value(): string|int|float|bool
    {
        return $this->value;
    }
}
