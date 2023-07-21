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
        $this->channel->exchange_declare(
            $message->getOption('exchangeName'),
            $message->getOption('exchangeType', AMQPExchangeType::DIRECT),
            false,
            true,
            false
        );

        $this->channel->queue_declare(
            queue: $message->queueName,
            durable: true,
            auto_delete: false,
            arguments: $message->getOption('queue') ?
                new AMQPTable($message->getOption('queue')) :
                []
        );

        $this->channel->queue_bind(
            $message->queueName,
            $message->getOption('exchangeName'),
            $message->getOption('routingKey')
        );
        $this->channel->basic_publish(new AMQPMessage(
            (string)$message,
            ['delivery_mode' => 2]),
            $message->getOption('exchangeName'),
            $message->getOption('routingKey')
        );
    }

    public function consume(string $queueName, callable $callback, array $options = []): void
    {
        $this->channel->queue_declare($queueName, false, true, false, false);
        $this->channel->basic_consume(
            queue: $queueName,
            no_ack: true,
            callback: $callback
        );
    }

    public function startWorker(): void
    {
        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        $this->channel->close();
        $this->connection->close();
    }

    public function publishInBatch(QueueMessageCollection $collection): void
    {
        foreach ($collection as $message) {
            $this->publish($message);
        }
    }
}
