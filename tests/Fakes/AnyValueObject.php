<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use DomainException;
use Astrotech\Core\Base\Domain\ValueObjectBase;

final class AnyValueObject extends ValueObjectBase
{
    public string $publicProp = 'any public value';
    private string $protectedProp = 'any protected value';
    private string $privateProp;

    public function __construct(string $privateProp)
    {
        if ($privateProp === 'invalid-value') {
            throw new DomainException('Invalid value to Value Object');
        }

        $this->privateProp = $privateProp;
    }

    public function value(): string|int|float|bool
    {
        return $this->privateProp;
    }
}
