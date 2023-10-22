<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra;

use AMQPConnectionException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;

final class RabbitMqConnector
{
    private ?AMQPStreamConnection $connection = null;
    private ?AMQPChannel $channel = null;
    private array $exchanges = [];
    private array $fanOutExchanges = [];

    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function connect(): void
    {
        if ($this->connection) {
            return;
        }

        while (is_null($this->connection)) {
            try {
                $this->connection = new AMQPStreamConnection(
                    $this->host,
                    $this->port,
                    $this->username,
                    $this->password
                );

                if ($this->channel?->is_open()) {
                    $this->channel->close();
                }

                $this->channel = $this->connection->channel();

                foreach ($this->fanOutExchanges as $exchangeName) {
                    $this->channel->exchange_declare($exchangeName, AMQP_EX_TYPE_FANOUT, false, true, false);
                }

                foreach ($this->exchanges as $exchangeName => $queueConfig) {
                    foreach ($queueConfig as $queueData) {
                        $this->channel->exchange_declare($exchangeName, AMQP_EX_TYPE_DIRECT, false, true, false);
                        $this->channel->queue_declare($queueData['queue'], false, true, false, false);
                        $this->channel->queue_bind($queueData['queue'], $exchangeName, $queueData['routingKey']);
                    }
                }
            } catch (AMQPConnectionException | AMQPChannelClosedException $e) {
                echo $e->getMessage() . PHP_EOL;
                echo 'Trying to connect again in 1 second.';
                sleep(1);
            }
        }
    }

    public function registerExchange(string $exchangeName, array $queues): void
    {
        $this->exchanges[$exchangeName] = $queues;
    }

    public function registerFanOutExchange(string $exchangeName): void
    {
        $this->fanOutExchanges[] = $exchangeName;
    }

    public function consume(string $queueName, callable $callback): void
    {
        $this->channel->basic_consume(queue: $queueName, callback: $callback);

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }
    }

    public function getConnection(): ?AMQPStreamConnection
    {
        return $this->connection;
    }

    public function getChannel(): ?AMQPChannel
    {
        return $this->channel;
    }

    public function close(): void
    {
        $this->channel->close();
        $this->channel = null;

        $this->connection->close();
        $this->connection = null;
    }
}
