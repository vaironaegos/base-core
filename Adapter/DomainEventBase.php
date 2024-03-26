<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter;

use Astrotech\Core\Base\Domain\Contracts\DomainEvent;
use DateTimeImmutable;
use JsonSerializable;
use Stringable;

abstract class DomainEventBase implements DomainEvent, JsonSerializable, Stringable
{
    private array $extraData = [];
    private string $action = '';

    public function when(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }

    public function values(): array
    {
        $data = get_object_vars($this);
        unset($data['extraData'], $data['action']);

        if (!$this->action) {
            $this->action = strtolower(camelCaseToUnderscores(self::name()));
        }

        return [
            'name' => self::name(),
            'className' => get_called_class(),
            'when' => $this->when()->format(DATE_ATOM),
            'action' => $this->action,
            'processedAt' => null,
            'data' => [...$data, ...$this->extraData]
        ];
    }

    public function addData(string $key, mixed $value): void
    {
        $this->extraData[$key] = $value;
    }

    public static function name(): string
    {
        $className = get_called_class();
        return basename(str_replace('\\', '/', $className));
    }

    public function setAction(string $actionName): void
    {
        $this->action = $actionName;
    }

    public function jsonSerialize(): array
    {
        return $this->values();
    }

    public function __toString(): string
    {
        return json_encode($this->values());
    }
}
