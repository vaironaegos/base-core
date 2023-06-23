<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\ValueObjects;

use Astrotech\ApiBase\Domain\ValueObjectBase;
use DomainException;

final class Cnpj extends ValueObjectBase
{
    private string $cnpj = '';
    private bool $isValid = true;

    public function __construct(string $cnpj, bool $strict = true)
    {
        if (empty($cnpj)) {
            throw new DomainException("CNPJ cannot be null or empty");
        }

        $cnpjSanitized = (string)preg_replace('/[^a-zA-Z0-9]/', '', $cnpj);

        if (mb_strlen($cnpjSanitized) !== 14) {
            $this->isValid = false;
            if ($strict) {
                throw new DomainException("CNPJ should be 14 characters.");
            }

            return;
        }

        if (preg_match('/(\d)\1{10}/', $cnpjSanitized)) {
            $this->isValid = false;
            if ($strict) {
                throw new DomainException("CNPJ with repeated characters.");
            }

            return;
        }

        if (!$this->validate($cnpjSanitized)) {
            $this->isValid = false;
            if ($strict === true) {
                throw new DomainException('Invalid CPF.');
            }
        }

        $this->cnpj = $cnpjSanitized;
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param string $cpf
     * @return bool
     */
    private function validate(string $cnpj): bool
    {
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto)) {
            return false;
        }

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++) {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[13] == ($resto < 2 ? 0 : 11 - $resto)) {
            return true;
        };

        return false;
    }

    public function formatted(): string
    {
        return preg_replace("/(\d{3})(\d{3})(\d{3})(\d{2})/", "\$1.\$2.\$3-\$4", $this->cnpj);
    }

    public function value(): string|int|float|bool
    {
        return $this->cnpj;
    }
}
