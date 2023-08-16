<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\QueueConsumer;

use AMQPException;
use AMQPQueue;
use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
use Doctrine\DBAL\Exception\DriverException;
use GuzzleHttp\Exception\RequestException;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;
use PhpAmqpLib\Exception\AMQPProtocolException;
use PhpAmqpLib\Exception\AMQPRuntimeException;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;
use Throwable;

abstract class ConsumerBase
{
    protected string $queueName;
    protected string $rawMessageBody;
    protected array $messageBody;

    abstract protected function handle(): void;

    public function __construct(
        protected readonly ContainerInterface $container,
        protected readonly AMQPQueue $queue
    ) {
        $this->queueName = $queue->getName();
        $this->rawMessageBody = $queue->get()->getBody();
        $this->messageBody = json_decode($this->rawMessageBody, true)['data'];
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
            $this->message->getChannel()->basic_ack($this->message->getDeliveryTag());
        } catch (AMQPRuntimeException | AMQPProtocolException | AMQPConnectionClosedException $e) {
            $logMessage = "{$prefix} AMQP Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (DriverException $e) {
            $logMessage = "{$prefix} Database Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}" . PHP_EOL . $e->getQuery()->getSQL();
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            sleep(2);
            echo $logMessage;
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (RequestException $e) {
            $logMessage = "{$prefix} Request Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
            $this->message->getChannel()->basic_nack(delivery_tag: $this->message->getDeliveryTag(), requeue: true);
        } catch (Throwable $e) {
            $logMessage = "{$prefix} Generic Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
        }
    }
}
