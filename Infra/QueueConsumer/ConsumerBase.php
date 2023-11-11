<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\QueueConsumer;

use Throwable;
use DateTimeImmutable;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Doctrine\DBAL\Exception\DriverException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPProtocolException;
use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
use Astrotech\ApiBase\Exception\ValidationException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;

abstract class ConsumerBase
{
    protected string $queueName;
    protected AbstractConnection $connection;
    protected string $rawMessageBody;
    protected array $messageBody;
    protected array $metaBody;

    abstract protected function handle(): void;

    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly AMQPMessage $message,
        protected readonly string $traceId
    ) {
        $this->queueName = $message->getRoutingKey();
        $this->rawMessageBody = $message->getBody();
        $this->metaBody = json_decode($message->getBody(), true);
        $this->messageBody = $this->metaBody['data'];
        $this->connection = $message->getChannel()->getConnection();
    }

    public function execute(): void
    {
        /** @var LogSystem $logSystem */
        $logSystem = $this->container->get(LogSystem::class);

        $handlerName = get_called_class();

        $logSystem->trace(
            json_encode(
                ['handler' => $handlerName, 'queue' => $this->queueName, 'message' => $this->messageBody],
                JSON_PRETTY_PRINT
            ),
            ['category' => $this->traceId]
        );

        $errorHandler = function (
            Throwable $e,
            array $details = []
        ) use (
            $logSystem,
            $handlerName
        ): void {
            $data = [
                'date' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
                'handler' => $handlerName,
                'type' => get_class($e),
                'message' => "{$e->getMessage()}",
                'file' => "{$e->getFile()}:{$e->getLine()}",
                'stackTrace' => $e->getTrace(),
                'queueMessage' => json_encode($this->messageBody),
                ...$details
            ];

            $jsonPayload = json_encode($data, JSON_PRETTY_PRINT);

            if ($jsonPayload !== false) {
                $logSystem->error($jsonPayload, ['category' => $this->traceId]);
                return;
            }

            $output = sprintf(
                "[%s] %s (%s:%s)" . PHP_EOL . "%s",
                $handlerName,
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString()
            );

            $logSystem->error($output, ['category' => $this->traceId]);
        };

        try {
            $this->handle();

            $logSystem->trace(
                json_encode(['handler' => $handlerName, 'message' => 'Message processed!'], JSON_PRETTY_PRINT),
                ['category' => $this->traceId]
            );

            $this->message->ack();
        } catch (ValidationException $e) {
            $errorHandler($e);
            $this->message->ack();
        } catch (
            RequestException
            | ConnectException
            | AMQPRuntimeException
            | AMQPProtocolException
            | AMQPConnectionClosedException
            $e
        ) {
            $errorHandler($e);
            $this->message->nack(true);
        } catch (DriverException $e) {
            $errorHandler($e, ['query' => $e->getQuery()->getSQL(), 'values' => $e->getQuery()->getParams()]);
            $this->message->nack(true);
        } catch (Throwable $e) {
            $errorHandler($e);
            $this->message->nack(true);
        }
    }
}
