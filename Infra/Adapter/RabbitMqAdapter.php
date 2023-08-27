<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Infra\RabbitMqConnector;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueSystem;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessage;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessageCollection;

final class RabbitMqAdapter implements QueueSystem
{
    private AMQPChannel $channel;

    public function __construct(private AMQPStreamConnection $connection)
    {
        $this->channel = $this->connection->channel();
    }

    public function publish(QueueMessage $message): void
    {
        $this->channel = $this->connection->channel();

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
