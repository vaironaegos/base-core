<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessage;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessageCollection;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueSystem;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

final class RabbitMqAdapter implements QueueSystem
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USERNAME'],
            $_ENV['RABBITMQ_PASSWORD']
        );
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
