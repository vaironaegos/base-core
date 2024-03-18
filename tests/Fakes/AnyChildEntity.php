<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use Astrotech\Core\Base\Domain\EntityBase;

/**
 * Class AnyEntity
 * @package Tests\Unit\Shared\Domain\Fakes
 *
 * @property string $name;
 * @property int $age;
 * @property float $salary;
 * @property array $favoriteMusics;
 * @property AnyValueObject $type;
 */
final class AnyChildEntity extends EntityBase
{
    protected string $name;
    protected int $age;
    protected float $salary;
    protected array $favoriteMusics;
    protected AnyValueObject $type;
}
