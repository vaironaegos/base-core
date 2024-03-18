<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Doctrine\Sorteable;

use MongoDB\Client as MongoDbClient;
use Astrotech\Core\Base\Adapter\DtoBase;
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
