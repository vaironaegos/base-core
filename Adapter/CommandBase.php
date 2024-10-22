<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Adapter;

use Astrotech\Core\Base\Adapter\Contracts\Command;
use Astrotech\Core\Base\Domain\UseCase\UseCaseOutput;

abstract class CommandBase extends DtoBase implements Command
{
    public static function handle(...$args): UseCaseOutput
    {
        $commandInstance = new static(...$args);
        $handlerClassName = $commandInstance->handleClassName();
        $handler = app($handlerClassName);
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
