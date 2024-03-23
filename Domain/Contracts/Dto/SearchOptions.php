<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts\Dto;

use Astrotech\Core\Base\Adapter\DtoBase;

final class SearchOptions extends DtoBase
{
    public function __construct(
        public readonly array $filters = [],
        protected array|string $sort = [],
        public readonly int $page = 1,
        public readonly int $perPage = 40,
        public readonly bool $skipPagination = false
    ) {
        $this->sort = is_string($this->sort) ? explode(',', $this->sort) : $this->sort;
    }
}
