<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Doctrine\Sorteable;

use MongoDB\Client as MongoDbClient;
use Astrotech\ApiBase\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;

class InputData extends DtoBase
{
    public function __construct(
        protected ?Builder $builder = null,
        protected ?MongoDbClient $mongoDbClient = null,
        protected readonly array $params = []
    ) {
    }
}
