<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessage;
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

    public function publish(QueueMessage $inputData): void
    {
        $this->channel->exchange_declare(
            $inputData->getOption('exchangeName'),
            $inputData->getOption('exchangeType', AMQPExchangeType::DIRECT),
            false,
            true,
            false
        );

        $this->channel->queue_declare(
            queue: $inputData->queueName,
            durable: true,
            auto_delete: false,
            arguments: $inputData->getOption('queue') ?
                new AMQPTable($inputData->getOption('queue')) :
                []
        );

        $this->channel->queue_bind(
            $inputData->queueName,
            $inputData->getOption('exchangeName'),
            $inputData->getOption('routingKey')
        );
        $this->channel->basic_publish(new AMQPMessage(
            (string)$inputData,
            ['delivery_mode' => 2]),
            $inputData->getOption('exchangeName'),
            $inputData->getOption('routingKey')
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
}
