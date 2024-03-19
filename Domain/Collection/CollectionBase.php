<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Domain\Collection;

use Iterator;
use Countable;
use ArrayAccess;
use JsonSerializable;
use RuntimeException;

abstract class CollectionBase implements ArrayAccess, Countable, Iterator, JsonSerializable
{
    public function __construct(
        protected array $items = []
    ) {
    }

    abstract protected function className(): string;

    public function offsetExists($offset): bool
    {
        if (!is_numeric($offset) or !isset($this->items[$offset])) {
            return false;
        }

        return true;
    }

    public function offsetGet($offset): mixed
    {
        if (!isset($this->items[$offset])) {
            return null;
        }

        return $this->items[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $className = $this->className();

        if (!$value instanceof $className) {
            throw new RuntimeException("'{$value}' is not type of '{$className}' in collection.");
        }

        if (is_null($offset)) {
            $this->items[] = $value;
            return;
        }

        if (!is_numeric($offset) or (!$offset and $offset !== 0)) {
            return;
        }

        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function current(): mixed
    {
        return current($this->items);
    }

    public function next(): void
    {
        next($this->items);
    }

    public function key(): int
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        $key = key($this->items);
        return ($key !== null);
    }

    public function rewind(): void
    {
        reset($this->items);
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return ($this->count() == 0);
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->items[$key]);
    }

    public function __unset($key)
    {
        unset($this->items[$key]);
    }

    public function __serialize(): array
    {
        return $this->items;
    }

    public function __unserialize(array $data): void
    {
        $this->items = $data;
    }

    public function getItems(bool $asArray = false): array
    {
        if (!$asArray) {
            return $this->items;
        }

        $this->map(function ($item) {
            if (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }
            return (array)$item;
        });

        return $this->items;
    }

    public function map(callable $callback): void
    {
        $this->items = array_map($callback, $this->items);
    }

    public function mapAsReturn(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    public function filter(callable $callback): void
    {
        $this->items = array_filter($this->items, $callback);
    }

    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function forEach(callable $callback): void
    {
        foreach ($this->items as $key => $value) {
            $cbReturn = $callback($value, $key);
            if ($cbReturn === false) {
                break;
            }
        }
    }

    public function merge(CollectionBase $array): void
    {
        $this->items = array_merge($this->items, $array->getItems());
    }
}
