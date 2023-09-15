<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Paginatable;

use Astrotech\ApiBase\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;

class InputData extends DtoBase
{
    public function __construct(
        protected Builder $builder,
        protected readonly int $currentPage,
        protected readonly int $perPage = 40,
        protected readonly bool $skipPagination = false
    ) {
    }
}
