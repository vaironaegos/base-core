<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use DateTimeInterface;
use Astrotech\Core\Base\Adapter\DtoBase;

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
    ) {
    }

    public function getName(): string
    {
        return mb_strtoupper($this->name);
    }
}
