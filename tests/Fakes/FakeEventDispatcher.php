<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use Psr\EventDispatcher\EventDispatcherInterface;
use stdClass;

final class FakeEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): object
    {
        return new stdClass();
    }
}
