<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts;

interface QueueSystem
{
    public function publish(string $message, string $queueName, array $options = []): void;
    public function consume(string $queueName, callable $callback, array $options = []): void;
    public function startWorker(): void;
}
