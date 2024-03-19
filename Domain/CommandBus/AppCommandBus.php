<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\CommandBus;

use RuntimeException;
use Astrotech\Core\Base\Adapter\Contracts\Dto;
use Astrotech\Core\Base\Domain\Contracts\EventBus;
use Astrotech\Core\Base\Domain\Contracts\CommandBus;
use Astrotech\Core\Base\Domain\Contracts\CommandHandler;
use Astrotech\Core\Base\Domain\Contracts\EventStoreRepository;

final class AppCommandBus implements CommandBus
{
    public function __construct(
        private readonly array $handlers,
        private readonly EventBus $eventBus,
        private readonly ?EventStoreRepository $eventStoreRepo = null,
        private readonly string $methodName = 'handle'
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
