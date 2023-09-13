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
        protected readonly AMQPMessage $message
    ) {
        $this->queueName = $message->getRoutingKey();
        $this->rawMessageBody = $message->getBody();
        $this->metaBody = json_decode($message->getBody(), true);
        $this->messageBody = $this->metaBody['data'];
        $this->connection = $message->getChannel()->getConnection();
    }

    public function execute(): void
    {
        $logfilePath = LOGS_PATH . "/{$this->queueName}-consumer.log";
        $prefix = "[" . uniqid() . " | queue: {$this->queueName}]";

        if (!is_dir(LOGS_PATH)) {
            mkdir(LOGS_PATH);
        }

        /** @var LogSystem $logSystem */
        $logSystem = $this->container->get(LogSystem::class);
        $handlerName = get_called_class();
        $logMessage = "{$prefix} Starting handler {$handlerName}" . PHP_EOL . $this->rawMessageBody . PHP_EOL;
        $logSystem->debug($logMessage, ['filename' => $logfilePath]);
        echo $logMessage;

        try {
            $this->handle();
            $logMessage = "{$prefix} Message processed Successfully!" . PHP_EOL;
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;

            if ($this->message->getChannel()->is_open()) {
                $this->message->getChannel()->basic_ack($this->message->getDeliveryTag());
            }
        } catch (AMQPEmptyDeliveryTagException $e) {
            $logMessage = "{$prefix} Nonexistent Delivery Tag! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
        } catch (AMQPRuntimeException | AMQPProtocolException | AMQPConnectionClosedException $e) {
            $logMessage = "{$prefix} AMQP Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (DriverException $e) {
            $logMessage = "{$prefix} Database Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}" .
                PHP_EOL . $e->getQuery()->getSQL();
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            sleep(2);
            echo $logMessage;
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (RequestException | ConnectException $e) {
            $logMessage = "{$prefix} Request Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
            if ($this->message->getChannel()->is_open()) {
                $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
            }
        } catch (ConsumerException $e) {
            $logMessage = "{$prefix} Consumer Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;

            if ($this->message->getChannel()->is_open()) {
                $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
            }
        } catch (Throwable $e) {
            $logMessage = "{$prefix} Generic Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
        }
    }
}
