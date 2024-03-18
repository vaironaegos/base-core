<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Infra\Enum;

enum LogLevelEnum: int
{
    case FATAL = 0;
    case ERROR = 1;
    case WARNING = 2;
    case INFO = 3;
    case DEBUG = 4;
    case TRACE = 5;
}
