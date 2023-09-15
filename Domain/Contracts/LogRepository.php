<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\Contracts;

use Astrotech\ApiBase\Domain\Contracts\Dto\SearchOptions;

interface LogRepository
{
    public function search(SearchOptions $options): array;
    public function insert(array $data): string | int;
}
