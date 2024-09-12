<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Contracts;

use Astrotech\Core\Base\Domain\Contracts\Dto\SearchOptions;

interface LogRepository
{
    public function search(SearchOptions $options): array;
    public function insert(array $data): string | int;
}
