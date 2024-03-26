<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter;

use Astrotech\Core\Base\Domain\Contracts\DomainEvent;
use Astrotech\Core\Base\Domain\Contracts\EventBus;
use Astrotech\Core\Base\Exception\RuntimeException;
use Psr\Container\ContainerInterface;

final class DomainEventBus implements EventBus
{
    public function __construct(
        private readonly array $listeners,
        private readonly ContainerInterface $container,
        private readonly string $methodName = 'handle'
    ) {
    }

    /**
     * @param DomainEventBase $event
     * @return void
     * @throws RuntimeException
     */
    public function dispatch(DomainEvent $event): void
    {
        $eventClassName = get_class($event);

        if (!isset($this->listeners[$eventClassName])) {
            throw new RuntimeException("No event registered with name '{$eventClassName}'");
        }

        if (empty($this->listeners[$eventClassName])) {
            return;
        }

        foreach ($this->listeners[$eventClassName] as $listener) {
            if (!method_exists($listener, $this->methodName)) {
                throw new RuntimeException(sprintf(
                    "The '%s' method does not exist in listener class '%s'",
                    $this->methodName,
                    get_class($listener)
                ));
            }

            $listener = $this->container->get($listener);
            call_user_func_array([$listener, $this->methodName], [$event]);
        }
    }
}
