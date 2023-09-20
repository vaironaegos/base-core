<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\QueueConsumer;

use Astrotech\ApiBase\Infra\Exception\ConsumerException;
use GuzzleHttp\Exception\ConnectException;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Exception\AMQPEmptyDeliveryTagException;
use Throwable;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use GuzzleHttp\Exception\RequestException;
use Doctrine\DBAL\Exception\DriverException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Exception\AMQPProtocolException;
use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
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
        protected readonly string $traceId,
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
            json_encode(['handler' => $handlerName, 'queue' => $this->queueName, 'message' => $this->messageBody]),
            ['category' => $this->traceId]
        );

        try {
            $this->handle();

            $logSystem->trace(
                json_encode(['handler' => $handlerName, 'message' => 'Message processed Successfully!']),
                ['category' => $this->traceId]
            );

            if ($this->message->getChannel()->is_open()) {
                $this->message->getChannel()->basic_ack($this->message->getDeliveryTag());
            }
        } catch (AMQPEmptyDeliveryTagException $e) {
            $logSystem->error(
                json_encode([
                    'handler' => $handlerName,
                    'message' => "Nonexistent Delivery Tag! {$e->getMessage()}",
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'stackTrace' => $e->getTrace()
                ]),
                ['category' => $this->traceId]
            );
        } catch (AMQPRuntimeException | AMQPProtocolException | AMQPConnectionClosedException $e) {
            $logSystem->error(
                json_encode([
                    'handler' => $handlerName,
                    'message' => "AMQP Error! {$e->getMessage()}",
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'stackTrace' => $e->getTrace()
                ]),
                ['category' => $this->traceId]
            );
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (DriverException $e) {
            $logSystem->error(
                json_encode([
                    'handler' => $handlerName,
                    'message' => "Database Error! {$e->getMessage()}",
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'sql' => $e->getQuery()?->getSQL(),
                    'stackTrace' => $e->getTrace()
                ]),
                ['category' => $this->traceId]
            );
            sleep(2);
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (RequestException | ConnectException $e) {
            $logSystem->error(
                json_encode([
                    'handler' => $handlerName,
                    'message' => "Request Error! {$e->getMessage()}",
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'stackTrace' => $e->getTrace()
                ]),
                ['category' => $this->traceId]
            );

            if ($this->message->getChannel()->is_open()) {
                $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
            }
        } catch (ConsumerException $e) {
            $logSystem->error(
                json_encode([
                    'handler' => $handlerName,
                    'message' => "Consumer Error! {$e->getMessage()}",
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'stackTrace' => $e->getTrace()
                ]),
                ['category' => $this->traceId]
            );

            if ($this->message->getChannel()->is_open()) {
                $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
            }
        } catch (Throwable $e) {
            $logSystem->error(
                json_encode([
                    'handler' => $handlerName,
                    'message' => "Generic Error! {$e->getMessage()}",
                    'file' => "{$e->getFile()}:{$e->getLine()}",
                    'stackTrace' => $e->getTrace()
                ]),
                ['category' => $this->traceId]
            );
        }
    }
}
