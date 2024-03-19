<?php

namespace Astrotech\Core\Base\Tests\Fakes;

use Astrotech\Core\Base\Adapter\DtoBase;
use DateTimeInterface;

final class FakeDto extends DtoBase
{
    public function __construct(
        public readonly int $id,
        protected readonly string $name,
        public readonly float $amount,
        public readonly array $details,
        public readonly bool $isActive,
        public readonly DateTimeInterface $createdAt,
        public readonly ?FakeDto $dto = null,
    ){}

    public function getName(): string
    {
        return mb_strtoupper($this->name);
    }
}
