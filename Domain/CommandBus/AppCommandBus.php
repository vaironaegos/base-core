<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\CommandBus;

use Astrotech\ApiBase\Adapter\Contracts\Dto;
use Astrotech\ApiBase\Domain\Contracts\CommandBus;
use Astrotech\ApiBase\Domain\Contracts\CommandHandler;
use Astrotech\ApiBase\Domain\Contracts\EventBus;
use Astrotech\ApiBase\Domain\Contracts\EventStoreRepository;
use RuntimeException;

final class AppCommandBus implements CommandBus
{
    public function __construct(
        private readonly array $handlers,
        private readonly EventBus $eventBus,
        private readonly string $methodName = 'handle',
        private readonly ?EventStoreRepository $eventStoreRepo = null
    ) {
    }

    public function dispatch(Dto $command)
    {
        $commandName = get_class($command);

        if (!isset($this->handlers[$commandName])) {
            throw new RuntimeException("No handler registered for command '{$commandName}'");
        }

        /** @var CommandHandler $handler */
        $handler = $this->handlers[$commandName];
        $handler->setEventStoreRepo($this->eventStoreRepo);
        $handler->setEventBus($this->eventBus);

        if (!method_exists($handler, $this->methodName)) {
            throw new RuntimeException(sprintf(
                "The '%s' method does not exist in handler class '%s'",
                $this->methodName,
                get_class($handler)
            ));
        }

        return $handler->{$this->methodName}($command);
    }
}
