<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Paginatable;

use Astrotech\ApiBase\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\Client as MongoDbClient;

class InputData extends DtoBase
{
    public function __construct(
        protected readonly int $currentPage,
        protected ?Builder $builder = null,
        protected ?MongoDbClient $mongoDbClient = null,
        protected readonly int $perPage = 40,
        protected readonly bool $skipPagination = false
    ) {
    }
}
