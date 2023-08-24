<?php

declare(strict_types=1);

namespace Astrotech\ApiBase\Adapter\Contracts\QueueSystem;

use Astrotech\ApiBase\Adapter\DtoBase;

final class QueueMessage extends DtoBase
{
    public function __construct(
        public readonly string $queueName,
        public readonly QueueActions $action,
        public readonly array $data,
        protected readonly array $options = []
    ) {
    }

    public function getOption(string|int $optionKey, mixed $defaultValue = null): mixed
    {
        if (!isset($this->options[$optionKey])) {
            return $defaultValue;
        }

        return $this->options[$optionKey];
    }

    public function __toString(): string
    {
        return json_encode(['action' => $this->action, 'data' => $this->data]);
    }
}
