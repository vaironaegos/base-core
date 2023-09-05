<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Domain\CommandBus;

use Astrotech\ApiBase\Adapter\Contracts\Dto;
use Astrotech\ApiBase\Domain\Contracts\CommandBus;
use Astrotech\ApiBase\Domain\Contracts\CommandHandler;
use RuntimeException;

final class AppCommandBus implements CommandBus
{
    public function __construct(
        private array $handlers,
        private readonly string $methodName = 'handle'
    ) {
    }

    public function registerHandler(string $commandClassName, CommandHandler $handler): void
    {
        $this->handlers[$commandClassName] = $handler;
    }

    public function dispatch(Dto $command)
    {
        $commandName = get_class($command);

        if (!isset($this->handlers[$commandName])) {
            throw new RuntimeException("No handler registered for command '{$commandName}'");
        }

        /** @var object $handler */
        $handler = $this->handlers[$commandName];

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
