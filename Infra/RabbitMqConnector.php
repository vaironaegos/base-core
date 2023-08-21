<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra;

use AMQPConnectionException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPChannelClosedException;

final class RabbitMqConnector
{
    private ?AMQPStreamConnection $connection = null;

    public function __construct(
        private readonly string $host,
        private readonly string $port,
        private readonly string $username,
        private readonly string $password
    ) {
    }

    public function getConnection(): AMQPStreamConnection
    {
        if ($this->connection) {
            return $this->connection;
        }

        while (is_null($this->connection)) {
            try {
                $this->connection = new AMQPStreamConnection(
                    $this->host,
                    $this->port,
                    $this->username,
                    $this->password
                );
            } catch (AMQPConnectionException | AMQPChannelClosedException $e) {
                echo $e->getMessage() . PHP_EOL;
                echo 'Trying to connect again in 1 second.';
                sleep(1);
            }
        }

        return $this->connection;
    }
}
