<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Infra\QueueConsumer;

use Astrotech\ApiBase\Adapter\Contracts\LogSystem;
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
        protected readonly AMQPMessage $message
    ) {
        $this->queueName = $message->getRoutingKey();
        $this->rawMessageBody = $message->getBody();
        $this->messageBody = json_decode($message->getBody(), true)['data'];
    }

    public function execute(): void
    {
        $logfilePath = "{$this->queueName}-consumer.log";
        $prefix = "[" . uniqid() . " | queue: {$this->queueName}]";

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
        } catch (Throwable $e) {
            $logMessage = "{$prefix} Unexpected Error! {$e->getMessage()} - {$e->getFile()}:{$e->getLine()}";
            $logSystem->debug($logMessage, ['filename' => $logfilePath]);
            echo $logMessage;
            $this->message->getChannel()->basic_nack($this->message->getDeliveryTag(), false, true);
        }
    }
}
