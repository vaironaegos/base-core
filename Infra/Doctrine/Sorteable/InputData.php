<?php

namespace Astrotech\ApiBase\Infra\Doctrine\Sorteable;

use Astrotech\ApiBase\Adapter\DtoBase;
use Doctrine\ODM\MongoDB\Query\Builder;

class InputData extends DtoBase
{
    public function __construct(
        protected Builder $builder,
        protected readonly ?string $params = null
    ) {
    }
}
