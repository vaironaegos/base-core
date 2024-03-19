<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\QueueSystem\QueueMessageCollection;
use Astrotech\Core\Base\Adapter\Contracts\QueueSystem\QueueSystem;
use Astrotech\Core\Base\Adapter\QueueMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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
