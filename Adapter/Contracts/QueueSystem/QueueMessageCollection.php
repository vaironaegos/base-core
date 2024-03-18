<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts\QueueSystem;

use Astrotech\Core\Base\Utils\CollectionBase;

final class QueueMessageCollection extends CollectionBase
{
    protected function className(): string
    {
        return QueueMessage::class;
    }
}
