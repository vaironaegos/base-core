<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Doctrine\Paginatable;

use Astrotech\Core\Base\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;

class InputData extends DtoBase
{
    public function __construct(
        public readonly int $currentPage,
        public ?Builder $builder = null,
        public readonly int $perPage = 40,
        public readonly bool $skipPagination = false
    ) {
    }
}
