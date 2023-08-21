<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessage;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessageCollection;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueSystem;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class RabbitMqAdapter implements QueueSystem
{
    private AMQPChannel $channel;

    public function __construct(private readonly AMQPStreamConnection $connection)
    {
        $this->channel = $this->connection->channel();
    }

    public function publish(QueueMessage $message): void
    {
        $this->channel->basic_publish(
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
