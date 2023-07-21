<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts\QueueSystem;

interface QueueSystem
{
    public function publish(QueueMessage $inputData): void;
    public function consume(string $queueName, callable $callback, array $options = []): void;
    public function startWorker(): void;
}
