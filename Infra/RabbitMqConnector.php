<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra;

use Throwable;
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
        try {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->username,
                $this->password
            );

            $this->channel = $this->connection->channel();
            $this->channel->basic_qos(0, 1, false);

            // FanOut Exchange setup
            foreach ($this->fanOutExchanges as $exchangeName => $queueList) {
                $this->channel->exchange_declare($exchangeName, AMQP_EX_TYPE_FANOUT, false, true, false);
                foreach ($queueList as $queueName) {
                    $this->channel->queue_declare($queueName, false, true, false, false);
                    $this->channel->queue_bind($queueName, $exchangeName);
                }
            }

            // Direct Exchange setup
            foreach ($this->exchanges as $exchangeName => $queueConfig) {
                foreach ($queueConfig as $queueData) {
                    $this->channel->exchange_declare($exchangeName, AMQP_EX_TYPE_DIRECT, false, true, false);
                    $this->channel->queue_declare($queueData['queue'], false, true, false, false);
                    $this->channel->queue_bind($queueData['queue'], $exchangeName, $queueData['routingKey']);
                }
            }
        } catch (AMQPConnectionException | AMQPChannelClosedException | Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            echo 'Trying to connect again in 1 second.';
            sleep(1);
        }
    }

    public function registerExchange(string $exchangeName, array $queues): void
    {
        $this->exchanges[$exchangeName] = $queues;
    }

    public function registerFanOutExchange(string $exchangeName, array $queues): void
    {
        $this->fanOutExchanges[$exchangeName] = $queues;
    }

    public function consume(string $queueName, callable $callback): void
    {
        if (is_null($this->channel)) {
            return;
        }

        $this->channel->basic_consume(queue: $queueName, callback: $callback);

        echo "[" . date('Y-m-d H:i:s') . "] App Consumer {$this->channel->getChannelId()} is ready." . PHP_EOL;

        while ($this->channel->is_open()) {
            $this->channel->wait();
        }

        echo "[" . date('Y-m-d H:i:s') . "] Closing consumer connection and channel." . PHP_EOL;
        $this->close();
    }

    public function getConnection(): AMQPStreamConnection
    {
        return $this->connection;
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    public function close(): void
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function isConnected(): bool
    {
        return $this->connection && $this->connection->isConnected();
    }
}
