<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra;

use Astrotech\Core\Base\Adapter\Contracts\LogSystem;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Container\ContainerInterface;

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

        $this->handle();

        $logSystem->trace(
            json_encode(['handler' => $handlerName, 'message' => 'Message processed!'], JSON_PRETTY_PRINT),
            ['category' => $this->traceId]
        );
    }
}
