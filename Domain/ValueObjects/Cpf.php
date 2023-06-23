<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\ValueObjects;

use Astrotech\ApiBase\Domain\ValueObjectBase;
use DomainException;

final class Cpf extends ValueObjectBase
{
    private string $cpf = '';
    private bool $isValid = true;

    public function __construct(string $cpf, bool $strict = true)
    {
        if (empty($cpf)) {
            throw new DomainException("CPF cannot be null or empty");
        }

        $cpfSanitized = (string)preg_replace('/[^a-zA-Z0-9]/', '', $cpf);

        if (mb_strlen($cpfSanitized) !== 11) {
            $this->isValid = false;
            if ($strict) {
                throw new DomainException("CPF should be 11 characters.");
            }

            return;
        }

        if (preg_match('/(\d)\1{10}/', $cpfSanitized)) {
            $this->isValid = false;
            if ($strict) {
                throw new DomainException("CPF with repeated characters.");
            }

            return;
        }

        if (!$this->validate($cpfSanitized)) {
            $this->isValid = false;
            if ($strict === true) {
                throw new DomainException('Invalid CPF.');
            }
        }

        $this->cpf = $cpfSanitized;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param string $cpf
     * @return bool
     */
    private function validate(string $cpf): bool
    {
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += (int)$cpf[$c] * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    public function formatted(): string
    {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $this->cpf);
    }

    public function value(): string|int|float|bool
    {
        return $this->cpf;
    }
}
