<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueSystem;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessage;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessageCollection;

final class RabbitMqAdapter implements QueueSystem
{
    public function __construct(
        private readonly AMQPStreamConnection $connection
    ) {
    }

    public function publish(QueueMessage $message): void
    {
        $this->connection->channel()->basic_publish(
            new AMQPMessage((string)$message, ['delivery_mode' => 2]),
            $message->getOption('exchangeName'),
            $message->getOption('routingKey')
        );
    }

    public function publishInBatch(QueueMessageCollection $messageCollection): void
    {
        foreach ($messageCollection as $message) {
            $this->publish($message);
        }
    }
}
