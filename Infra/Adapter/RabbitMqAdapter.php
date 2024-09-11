<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\QueueSystem\QueueMessage;
use Astrotech\Core\Base\Adapter\Contracts\QueueSystem\QueueSystem;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class RabbitMqAdapter implements QueueSystem
{
    public function __construct(
        private readonly AMQPStreamConnection $connection
    ) {
    }

    public function publish(QueueMessage $message, array $options = []): void
    {
        $this->connection->channel()->basic_publish(
            new AMQPMessage($message, ['delivery_mode' => 2]),
            $message->getOption('exchangeName'),
            $message->getOption('routingKey')
        );
    }

    public function consume(string $queueName, callable $callback, array $options = []): void
    {
    }
}
