<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\LogSystem;
use Astrotech\Core\Base\Adapter\Contracts\QueueSystem;
use Exception;
use Throwable;
use AMQPConnectionException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;

final class RabbitMqAdapter implements QueueSystem
{
    private AMQPStreamConnection $connection;
    private AMQPChannel $channel;
    private string $exchangeName;
    private string $queueName;
    private string $routingKey;

    public function __construct(
        private readonly LogSystem $logSystem
    ) {
    }

    private function connect(): void
    {
        if (isset($this->connection)) {
            if (!isset($this->channel) || !$this->channel->is_open()) {
                $this->channel = $this->connection->channel();
            }

            return;
        }

        while (!isset($this->connection)) {
            try {
                $this->connection = new AMQPStreamConnection(
                    host: env('RABBITMQ_HOST'),
                    port: env('RABBITMQ_PORT') ?? 5672,
                    user: env('RABBITMQ_USERNAME'),
                    password: env('RABBITMQ_PASSWORD'),
                    keepalive: true
                );
                $this->channel = $this->connection->channel();
                $this->channel->basic_qos(0, 1, false);
            } catch (AMQPConnectionException | AMQPChannelClosedException | Throwable $e) {
                echo $e->getMessage() . PHP_EOL;
                echo 'Trying to connect again in 1 second.';
                sleep(1);
            }
        }
    }

    public function prepareChannel(string $queueName, string $exchangeName, ?string $routingKey = null): void
    {
        $this->connect();
        $this->channel->queue_declare($queueName, durable: true, auto_delete: false);
        $this->channel->exchange_declare($exchangeName, AMQPExchangeType::DIRECT, durable: true, auto_delete: false);
        $this->channel->queue_bind(
            $queueName,
            $exchangeName,
            !is_null($routingKey) ? $routingKey : $queueName
        );

        $this->queueName = $queueName;
        $this->exchangeName = $exchangeName;
        $this->routingKey = !is_null($routingKey) ? $routingKey : $queueName;
    }

    public function publish(string $message, string $queueName, array $options = []): void
    {
        $options['exchangeName'] = env('APP_NAME');

        $this->connect();

        $msg = new AMQPMessage((string)$message);
        $routingKey = $options['routingKey'] . "_{$queueName}";

        $this->prepareChannel($queueName, $options['exchangeName'], $routingKey);
        $this->channel->basic_publish(
            $msg,
            $options['exchangeName'],
            $routingKey
        );

        $this->channel->close();
        //        $this->connection->close();
    }

    public function publishInBatch(array $messages): void
    {
        foreach ($messages as $dataMessage) {
            $this->publish($dataMessage['message'], $dataMessage['queue'], $dataMessage['options']);
        }
    }

    public function consume(string $queueName, callable $callback, array $options = []): void
    {
        if (empty($this->queueName)) {
            throw new Exception('queueNotDeclared');
        }

        $this->channel->basic_consume(
            $this->queueName,
            callback: function ($message) use ($callback) {
                $this->logSystem->debug($message->getBody());
                $callback($message);
            }
        );
    }

    public function getChannel(): AMQPChannel
    {
        return $this->channel;
    }

    public function getRoutingKey(): string
    {
        return $this->routingKey;
    }
}
