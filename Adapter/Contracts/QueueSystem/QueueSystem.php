<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter\Contracts\QueueSystem;

interface QueueSystem
{
    /**
     * Publish a message to the specified queue.
     * @param QueueMessage $message The message to be published.
     * @param array $options Optional options for publishing the message.
     * @return void
     */
    public function publish(QueueMessage $message, array $options = []): void;

    /**
     * Consume messages from the specified queue and process them using the provided callback.
     * @param string $queueName The name of the queue to consume messages from.
     * @param callable $callback The callback function to process each consumed message.
     * @param array $options Optional options for consuming messages.
     * @return void
     */
    public function consume(string $queueName, callable $callback, array $options = []): void;
}
