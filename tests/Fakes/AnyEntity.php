<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Fakes;

use Astrotech\Core\Base\Domain\EntityBase;
use DateTimeInterface;

/**
 * @property string $stringProp;
 * @property string|null $nullableProp;
 * @property int $intProp;
 * @property float $floatProp;
 * @property array $arrayProp;
 * @property AnyValueObject $valueObjectProp;
 * @property DateTimeInterface $dateProp;
 * @property DateTimeInterface $datetimePropUs;
 * @property DateTimeInterface $datetimePropIso;
 * @property AnyChildEntity $childEntity;
 * @property AnyChildEntity|null $childEntityNullable;
 * @property GenericClass|null $genericObject;
 */
final class AnyEntity extends EntityBase
{
    protected string $stringProp;
    protected ?string $nullableProp;
    protected int $intProp;
    protected float $floatProp;
    protected array $arrayProp;
    protected bool $boolProp;
    protected AnyValueObject $valueObjectProp;
    protected DateTimeInterface $dateProp;
    protected DateTimeInterface $datetimePropUs;
    protected DateTimeInterface $datetimePropIso;
    protected AnyChildEntity $childEntity;
    protected ?AnyChildEntity $childEntityNullable;
    protected ?GenericClass $genericObject;

    public function setStringProp(string $stringProp): AnyEntity
    {
        $this->stringProp = strtoupper($stringProp);
        return $this;
    }

    public function getNullableProp(): ?string
    {
        return is_null($this->nullableProp) ? null : strtoupper($this->nullableProp);
    }
}
