<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\QueueConsumer;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;

final class RabbitMqConnector
{
    private static ?AMQPStreamConnection $connection = null;

    public static function getConnection(string $host, string $username, string $password, int $port = 5672): AMQPStreamConnection
    {
        $rabbitMqConnect = fn (): AMQPStreamConnection => new AMQPStreamConnection(
            $host,
            $port,
            $username,
            $password
        );

        while (is_null(self::$connection)) {
            try {
                self::$connection = $rabbitMqConnect();
            } catch (AMQPIOException $e) {
                echo $e->getMessage() . PHP_EOL;
                echo 'Trying to connect again in 1 second.';
                sleep(1);
            }
        }

        return self::$connection;
    }
}
