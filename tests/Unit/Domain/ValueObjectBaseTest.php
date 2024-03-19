<?php

namespace Astrotech\Core\Base\tests\Unit\Domain;

use Astrotech\Core\Base\Tests\Fakes\AnyValueObject;
use Astrotech\Core\Base\Tests\TestCase;

final class ValueObjectBaseTest extends TestCase
{
    public function testItShouldReturnValueWhenItsValueMethodIsCalled()
    {
        $value = self::$faker->words(2, true);
        $sut = new AnyValueObject($value);

        $this->assertSame($sut->value(), $value);
    }

    public function testItShouldReturnValueWhenItsConvertedToString()
    {
        $value = self::$faker->words(2, true);
        $sut = new AnyValueObject($value);

        $this->assertSame((string)$sut, $value);
    }

    public function testItShouldReturnValueWhenItsConvertedToJson()
    {
        $value = self::$faker->words(2, true);
        $sut = new AnyValueObject($value);
        $json = json_encode($sut);

        $this->assertSame($json, "\"{$value}\"");
    }

    public function testItShouldReturnTrueWhenTwoValueObjectsAreEqual()
    {
        $value = self::$faker->words(2, true);
        $sut = new AnyValueObject($value);
        $sut2 = new AnyValueObject($value);

        $this->assertTrue($sut->isEqualsTo($sut2));
    }
}
