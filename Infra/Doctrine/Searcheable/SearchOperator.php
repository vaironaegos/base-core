<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Doctrine\Searcheable;

enum SearchOperator: string
{
    case EQUAL = 'eq';
    case NOT_EQUAL = 'neq';
    case IN = 'in';
    case NOT_IN = 'nin';
    case LIKE = 'like';
    case LESS_THAN = 'lt';
    case GREATER_THAN = 'gt';
    case LESS_THAN_EQUAL = 'lte';
    case GREATER_THAN_EQUAL = 'gte';
    case BETWEEN = 'btw';
}
