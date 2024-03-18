<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts\QueueSystem;

interface QueueSystem
{
    public function publish(QueueMessage $message): void;
    public function publishInBatch(QueueMessageCollection $messageCollection): void;
}
