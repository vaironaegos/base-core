<?php

namespace Astrotech\ApiBase\Infra\Doctrine\Sorteable;

use Astrotech\ApiBase\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\Client as MongoDbClient;

class InputData extends DtoBase
{
    public function __construct(
        protected ?Builder $builder = null,
        protected ?MongoDbClient $mongoDbClient = null,
        protected readonly array $params = []
    ) {
    }
}
