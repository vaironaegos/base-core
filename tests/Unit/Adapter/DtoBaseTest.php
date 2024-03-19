<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Unit\Adapter;

use Astrotech\Core\Base\Adapter\DtoBase;
use Astrotech\Core\Base\Adapter\Exception\InvalidDtoParamException;
use Astrotech\Core\Base\Tests\Fakes\FakeDto;
use Astrotech\Core\Base\Tests\TestCase;
use DateTimeImmutable;

final class DtoBaseTest extends TestCase
{
    public function testItShouldThrowExceptionWhenInvalidDtoPropertyIsGet()
    {
        $this->expectException(InvalidDtoParamException::class);

        $id = self::$faker->randomDigit();
        $name = self::$faker->firstName();
        $amount = self::$faker->randomFloat();
        $details = ['a' => 1, 'b' => 2];
        $createdAt = new DateTimeImmutable();

        $sut = new FakeDto($id, $name, $amount, $details, true, $createdAt);
        $sut->invalidProperty;
    }

    public function testItShouldCreateADtoObjectWhenCreatedByConstructorMethod()
    {
        $id = self::$faker->randomDigit();
        $name = self::$faker->firstName();
        $amount = self::$faker->randomFloat();
        $details = ['a' => 1, 'b' => 2];
        $createdAt = new DateTimeImmutable();

        $values = [
            'id' => $id,
            'name' => $name,
            'amount' => $amount,
            'details' => $details,
            'isActive' => true,
            'createdAt' => $createdAt,
            'dto' => new FakeDto($id, $name, $amount, $details, true, $createdAt)
        ];

        $sut = new FakeDto(...$values);

        $this->assertSame($id, $sut->id);
        $this->assertSame(mb_strtoupper($name), $sut->name);
        $this->assertSame($amount, $sut->amount);
        $this->assertSame($details, $sut->details);
        $this->assertSame($createdAt, $sut->createdAt);
        $this->assertInstanceOf(DtoBase::class, $sut->dto);
    }

    public function testItShouldReturnTheDtoValuesAsArray()
    {
        $id = self::$faker->randomDigit();
        $name = self::$faker->firstName();
        $amount = self::$faker->randomFloat();
        $details = ['a' => 1, 'b' => 2];
        $createdAt = new DateTimeImmutable();

        $sut = new FakeDto($id, $name, $amount, $details, true, $createdAt);
        $values = $sut->values();

        $this->assertNotEmpty($values);
        $this->assertCount(7, $values);

        $this->assertArrayHasKey('id', $values);
        $this->assertArrayHasKey('name', $values);
        $this->assertArrayHasKey('amount', $values);
        $this->assertArrayHasKey('details', $values);
        $this->assertArrayHasKey('createdAt', $values);
        $this->assertArrayHasKey('dto', $values);

        $this->assertSame($id, $values['id']);
        $this->assertSame(mb_strtoupper($name), $values['name']);
        $this->assertSame($amount, $values['amount']);
        $this->assertSame($details, $values['details']);
        $this->assertSame($createdAt, $values['createdAt']);
        $this->assertNull($values['dto']);
    }

    public function testItShouldReturnValuesOutputWhenObjectIsConvertedToJson()
    {
        $id = self::$faker->randomDigit();
        $name = self::$faker->firstName();
        $amount = self::$faker->randomFloat();
        $details = ['a' => 1, 'b' => 2];
        $createdAt = new DateTimeImmutable();

        $values = [
            'id' => $id,
            'name' => $name,
            'amount' => $amount,
            'details' => $details,
            'isActive' => true,
            'createdAt' => $createdAt,
            'dto' => new FakeDto($id, $name, $amount, $details, true, $createdAt)
        ];

        $sut = new FakeDto(...$values);
        $json = json_encode($sut);
        $parsedJson = json_decode($json, true);

        $this->assertSame($sut->id, $parsedJson['id']);
        $this->assertSame($sut->name, $parsedJson['name']);
        $this->assertEquals($sut->amount, $parsedJson['amount']);
        $this->assertSame($sut->details, $parsedJson['details']);
        $this->assertSame($sut->isActive, $parsedJson['isActive']);
        $this->assertSame($sut->createdAt->format('Y-m-d H:i:s'), $parsedJson['createdAt']);
        $this->assertSame($sut->dto->toArray(), $parsedJson['dto']);
    }
}
