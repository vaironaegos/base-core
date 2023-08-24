<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts\QueueSystem;

use Astrotech\ApiBase\Utils\CollectionBase;

final class QueueMessageCollection extends CollectionBase
{
    protected function className(): string
    {
        return QueueMessage::class;
    }
}
