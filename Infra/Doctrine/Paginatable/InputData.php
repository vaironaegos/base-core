<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Paginatable;

use Astrotech\ApiBase\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\Client as MongoDbClient;

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
