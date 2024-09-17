<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra;

use Psr\Container\ContainerInterface;
use Throwable;
use DateTimeImmutable;
use PhpAmqpLib\Message\AMQPMessage;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Doctrine\DBAL\Exception\DriverException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPProtocolException;
use Astrotech\Core\Base\Adapter\Contracts\LogSystem;
use Astrotech\Core\Base\Exception\ValidationException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;

abstract class ConsumerBase
{
    protected string $queueName;
    protected AbstractConnection $connection;
    protected string $rawMessageBody;
    protected array $messageBody;
    protected array $metaBody;
    protected bool $hasError = false;

    abstract protected function handle(): void;

    public function __construct(
        protected readonly AMQPMessage $message,
        protected readonly ContainerInterface $container,
        protected readonly string $traceId = '',
    ) {
        $this->queueName = $message->getRoutingKey();
        $this->connection = $message->getChannel()->getConnection();
        $this->rawMessageBody = $message->getBody();
        $this->metaBody = json_decode($message->getBody(), true);
        $this->messageBody = $this->metaBody['data'];

        // to cover when Domain Event Class structure
        if (isset($this->messageBody['data']) && isset($this->messageBody['when'])) {
            $this->messageBody = $this->messageBody['data'];
        }
    }

    public function execute(): void
    {
        $handlerName = get_called_class();
        $logSystem = $this->container->get(LogSystem::class);

        $logSystem->debug(
            json_encode(
                ['handler' => $handlerName, 'queue' => $this->queueName, 'message' => $this->messageBody],
                JSON_PRETTY_PRINT
            ),
            ['category' => "{$this->queueName}_'consumer'"]
        );

        $errorHandler = function (
            Throwable $e,
            array $details = []
        ) use (
            $handlerName,
            $logSystem
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
                $logSystem->error($jsonPayload);
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

            $logSystem->error($output, ['category' => "{$this->queueName}_'consumer'"]);
        };

        try {
            $this->handle();

            $logSystem->info(
                json_encode(['handler' => $handlerName, 'message' => 'Message processed!'], JSON_PRETTY_PRINT)
            );

            $this->metaBody['data'] = $this->messageBody;
            $this->message->setBody(json_encode($this->metaBody));
        } catch (ValidationException $e) {
            $errorHandler($e);
            $this->message->ack();
            $this->hasError = true;
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
            $this->hasError = true;
        } catch (DriverException $e) {
            $errorHandler($e, ['query' => $e->getQuery()->getSQL(), 'values' => $e->getQuery()->getParams()]);
            $this->message->nack(true);
            $this->hasError = true;
        } catch (Throwable $e) {
            $errorHandler($e);
            $this->message->nack(true);
            $this->hasError = true;
        }
    }

    public function hasError(): bool
    {
        return $this->hasError;
    }
}
