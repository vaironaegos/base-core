<?php

namespace Astrotech\ApiBase\Domain\Contracts\Dto;

use Astrotech\ApiBase\Adapter\DtoBase;

class SearchOptions extends DtoBase
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
