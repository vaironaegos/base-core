<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\Adapter;

use AMQPChannel;
use AMQPConnection;
use AMQPExchange;
use AMQPQueue;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessage;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueMessageCollection;
use Astrotech\ApiBase\Adapter\Contracts\QueueSystem\QueueSystem;

final class RabbitMqAdapter implements QueueSystem
{
    private AMQPConnection $connection;
    private AMQPChannel $channel;

    public function __construct()
    {
        $this->connection = new AMQPConnection([
            'host' => $_ENV['RABBITMQ_HOST'],
            'port' => $_ENV['RABBITMQ_PORT'],
            'login' => $_ENV['RABBITMQ_USERNAME'],
            'password' => $_ENV['RABBITMQ_PASSWORD'],
            'vhost' => '/'
        ]);

        $this->connection->connect();

        $this->channel = new AMQPChannel($this->connection);
    }

    public function publish(QueueMessage $message): void
    {
        $exchange = new AMQPExchange($this->channel);
        $exchange->setName($message->getOption('exchangeName'));
        $exchange->setType($message->getOption('exchangeType', AMQP_EX_TYPE_DIRECT));
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declareExchange();

        $queue = new AMQPQueue($this->channel);
        $queue->setName($message->queueName);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declareQueue();
        $queue->bind($exchange->getName(), $queue->getName());

        $exchange->publish((string)$message, $queue->getName(), AMQP_NOPARAM, [
            'delivery_mode' => 2
        ]);
    }

    public function publishInBatch(QueueMessageCollection $messageCollection): void
    {
        foreach ($messageCollection as $message) {
            $this->publish($message);
        }
    }
}
