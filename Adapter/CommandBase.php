<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\Command;

abstract class CommandBase extends DtoBase implements Command
{
    public static function handle(...$args): mixed
    {
        $commandInstance = new static(...$args);
        $handlerClass = $commandInstance->handleClassName();
        $handler = new $handlerClass();
        return $handler->execute($commandInstance);
    }

    public function handleClassName(): string
    {
        $commandFullClassName = get_called_class();
        $commandClassName = substr(strrchr($commandFullClassName, '\\'), 1);
        $lastBackslashPosition = strrpos($commandFullClassName, '\\');
        $namespace = substr($commandFullClassName, 0, $lastBackslashPosition);
        $handlerClassName = str_replace('Command', 'Handler', $commandClassName);

        return $namespace . '\\' . $handlerClassName;
    }
}
