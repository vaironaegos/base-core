<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests\Unit\Domain;

use Astrotech\Core\Base\Domain\Contracts\Entity;
use Astrotech\Core\Base\Domain\Contracts\ValueObject;
use Astrotech\Core\Base\Domain\EntityBase;
use Astrotech\Core\Base\Domain\Exceptions\EntityException;
use DateTimeImmutable;
use DateTimeInterface;
use Astrotech\Core\Base\Tests\Fakes\AnyChildEntity;
use Astrotech\Core\Base\Tests\Fakes\AnyEntity;
use Astrotech\Core\Base\Tests\Fakes\AnyValueObject;
use Astrotech\Core\Base\Tests\Fakes\GenericClass;
use Astrotech\Core\Base\Tests\TestCase;

final class EntityBaseTest extends TestCase
{
    public static function makeSut(array $values = []): EntityBase
    {
        if (!empty($values)) {
            return new AnyEntity($values);
        }

        return new AnyEntity([
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => null,
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'boolProp' => true,
            'valueObjectProp' => 'any-value-object',
            'dateProp' => (new DateTimeImmutable())->format('Y-m-d'),
            'datetimePropUs' => (new DateTimeImmutable())->format('Y-m-d h:i:s'),
            'datetimePropIso' => (new DateTimeImmutable())->format(DATE_ATOM),
            'genericObject' => new GenericClass(),
            'childEntity' => [
                'name' => self::$faker->words(2, true),
                'age' => self::$faker->randomDigit(),
                'salary' => self::$faker->randomFloat(),
                'favoriteMusics' => ['music1', 'music2', 'music3'],
                'type' => 'any-value-object'
            ],
        ]);
    }

    public function testIfPropertyWithUnderscoreSeparatorWillPopulateEntity()
    {
        $values = [
            'id' => self::$faker->uuid(),
            'string_prop' => self::$faker->words(2, true),
            'nullable_prop' => self::$faker->words(2, true),
            'int_prop' => self::$faker->randomDigit(),
            'float_prop' => self::$faker->randomFloat(),
            'array_prop' => ['a', 'b', 'c', 'd'],
            'bool_prop' => false,
            'value_object_prop' => 'any value'
        ];

        $sut = self::makeSut($values);

        $this->assertSame($values['id'], $sut->getId());
        $this->assertSame(strtoupper($values['string_prop']), $sut->get('stringProp'));
        $this->assertSame(strtoupper($values['nullable_prop']), $sut->get('nullableProp'));
        $this->assertSame($values['int_prop'], $sut->get('intProp'));
        $this->assertSame($values['float_prop'], $sut->get('floatProp'));
        $this->assertSame($values['array_prop'], $sut->get('arrayProp'));
        $this->assertFalse($sut->get('boolProp'));
        $this->assertSame($values['value_object_prop'], $sut->get('valueObjectProp')->value());
    }

    public function testItShouldReturnGetMethodLogicWhenItsImplemented()
    {
        $values = [
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => self::$faker->words(2, true),
        ];

        $sut = self::makeSut($values);

        $this->assertSame(strtoupper($values['stringProp']), $sut->get('stringProp'));
        $this->assertSame(strtoupper($values['nullableProp']), $sut->get('nullableProp'));
    }

    public function testIfExceptionIsThrownWhenNonexistentPropertyIsCalled()
    {
        $this->expectException(EntityException::class);
        $sut = self::makeSut();

        /** @noinspection PhpExpressionResultUnusedInspection */
        $sut->invalidProp;
    }

    public function testIfNullablePropertyIsNotFilledWhenNullValueIsProvided()
    {
        $sut = self::makeSut(['nullableProp' => null]);
        $this->assertSame(null, $sut->get('nullableProp'));
    }

    public function testItShouldConvertDateTimePropertiesToDateTimeObjectsWhenStringPatternIsPassed()
    {
        $sut = static::makeSut();
        $this->assertInstanceOf(DateTimeInterface::class, $sut->get('dateProp'));
        $this->assertInstanceOf(DateTimeInterface::class, $sut->get('datetimePropUs'));
        $this->assertInstanceOf(DateTimeInterface::class, $sut->get('datetimePropIso'));
    }

    public function testItShouldCreateChildEntityWhenItsValuesIsPassedAsArray()
    {
        $sut = static::makeSut();
        $childEntity = $sut->get('childEntity');

        $this->assertInstanceOf(AnyChildEntity::class, $childEntity);
        $this->assertNotEmpty($childEntity->get('name'));
        $this->assertIsInt($childEntity->get('age'));
        $this->assertNotEmpty($childEntity->get('salary'));
        $this->assertNotEmpty($childEntity->get('favoriteMusics'));
        $this->assertNotEmpty($childEntity->get('type'));
    }

    public function testItShouldCreateChildEntityWhenItsValuesIsPassedAsEntityObject()
    {
        $sut = new AnyEntity([
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => null,
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'boolProp' => false,
            'valueObjectProp' => new AnyValueObject('any-value'),
            'dateProp' => (new DateTimeImmutable())->format('Y-m-d'),
            'datetimePropUs' => (new DateTimeImmutable())->format('Y-m-d h:i:s'),
            'datetimePropIso' => (new DateTimeImmutable())->format(DATE_ATOM),
            'childEntity' => new AnyChildEntity([
                'name' => self::$faker->words(2, true),
                'age' => self::$faker->randomDigit(),
                'salary' => self::$faker->randomFloat(),
                'favoriteMusics' => ['music1', 'music2', 'music3'],
                'type' => new AnyValueObject('any-value')
            ])
        ]);

        $childEntity = $sut->get('childEntity');

        $this->assertInstanceOf(AnyChildEntity::class, $childEntity);
        $this->assertNotEmpty($childEntity->get('name'));
        $this->assertIsInt($childEntity->get('age'));
        $this->assertNotEmpty($childEntity->get('salary'));
        $this->assertNotEmpty($childEntity->get('favoriteMusics'));
        $this->assertNotEmpty($childEntity->get('type'));
    }

    public function testItShouldCreateValueObjectInstanceWhenStringIsProvided()
    {
        $sut = static::makeSut();

        /** @var ValueObject $valueObject */
        $valueObject = $sut->get('valueObjectProp');
        $childValueObject = $sut->get('childEntity')->type;

        $this->assertInstanceOf(AnyValueObject::class, $valueObject);
        $this->assertInstanceOf(AnyValueObject::class, $childValueObject);
        $this->assertSame($valueObject->value(), 'any-value-object');
        $this->assertSame($childValueObject->value(), 'any-value-object');
    }

    public function testItShouldCreateValueObjectInstanceWhenInstanceOfValueObjectIsProvided()
    {
        $sut = new AnyEntity([
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => null,
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'boolProp' => false,
            'valueObjectProp' => new AnyValueObject('any-value-object'),
            'dateProp' => (new DateTimeImmutable())->format('Y-m-d'),
            'datetimePropUs' => (new DateTimeImmutable())->format('Y-m-d h:i:s'),
            'datetimePropIso' => (new DateTimeImmutable())->format(DATE_ATOM),
            'childEntity' => [
                'name' => self::$faker->words(2, true),
                'age' => self::$faker->randomDigit(),
                'salary' => self::$faker->randomFloat(),
                'favoriteMusics' => ['music1', 'music2', 'music3'],
                'type' => new AnyValueObject('any-value-object')
            ],
        ]);

        /** @var ValueObject $valueObject */
        $valueObject = $sut->get('valueObjectProp');
        $childValueObject = $sut->get('childEntity')->type;

        $this->assertInstanceOf(AnyValueObject::class, $valueObject);
        $this->assertInstanceOf(AnyValueObject::class, $childValueObject);
    }

    public function testIfValuesIsChangedWhenNewValuesIsProvided()
    {
        $sut = self::makeSut([
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => null,
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'valueObjectProp' => 'any value'
        ]);

        $values = [
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => self::$faker->words(2, true),
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'valueObjectProp' => 'any value'
        ];

        $sut->fill($values);

        $this->assertSame(strtoupper($values['stringProp']), $sut->get('stringProp'));
        $this->assertSame(strtoupper($values['nullableProp']), $sut->get('nullableProp'));
        $this->assertSame($values['intProp'], $sut->get('intProp'));
        $this->assertSame($values['floatProp'], $sut->get('floatProp'));
        $this->assertSame($values['arrayProp'], $sut->get('arrayProp'));
        $this->assertSame($values['valueObjectProp'], $sut->get('valueObjectProp')->value());
    }

    public function testIfValuesIsReturnedCorrectlyWhenToArrayIsPassedWithToSnakeCaseFlag()
    {
        $values = [
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => self::$faker->words(2, true),
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'boolProp' => false,
            'valueObjectProp' => 'any value'
        ];

        $sut = self::makeSut($values);
        $data = $sut->toArray(true);

        $this->assertArrayHasKey('string_prop', $data);
        $this->assertArrayHasKey('nullable_prop', $data);
        $this->assertArrayHasKey('int_prop', $data);
        $this->assertArrayHasKey('float_prop', $data);
        $this->assertArrayHasKey('array_prop', $data);
        $this->assertArrayHasKey('value_object_prop', $data);

        $this->assertSame(strtoupper($values['stringProp']), $data['string_prop']);
        $this->assertSame($values['nullableProp'], $data['nullable_prop']);
        $this->assertSame($values['intProp'], $data['int_prop']);
        $this->assertEquals($values['floatProp'], $data['float_prop']);
        $this->assertSame($values['arrayProp'], $data['array_prop']);
        $this->assertFalse($data['bool_prop']);

        $this->assertSame($values['valueObjectProp'], $data['value_object_prop']);
    }

    public function testIfEntityIsReturnedAsArrayCorrectly()
    {
        $values = [
            'stringProp' => self::$faker->words(2, true),
            'nullableProp' => self::$faker->words(2, true),
            'intProp' => self::$faker->randomDigit(),
            'floatProp' => self::$faker->randomFloat(),
            'arrayProp' => ['a', 'b', 'c', 'd'],
            'boolProp' => false,
            'valueObjectProp' => new AnyValueObject('any value')
        ];

        $sut = self::makeSut($values);
        $array = $sut->toArray();

        $this->assertSame(strtoupper($values['stringProp']), $array['stringProp']);
        $this->assertSame($values['nullableProp'], $array['nullableProp']);
        $this->assertSame($values['intProp'], $array['intProp']);
        $this->assertEquals($values['floatProp'], $array['floatProp']);
        $this->assertSame($values['arrayProp'], $array['arrayProp']);
        $this->assertFalse($array['boolProp']);

        $this->assertSame('any value', $array['valueObjectProp']);
    }

    public function testItShouldReturnTrueValuesWhenSynonymousValuesIsProvided()
    {
        $sut = self::makeSut();

        $sut->set('boolProp', 1);
        $this->assertTrue($sut->get('boolProp'));

        $sut->set('boolProp', '1');
        $this->assertTrue($sut->get('boolProp'));
    }

    public function testItShouldReturnFalseValuesWhenSynonymousValuesIsProvided()
    {
        $sut = self::makeSut();

        $sut->set('boolProp', 0);
        $this->assertFalse($sut->get('boolProp'));

        $sut->set('boolProp', '0');
        $this->assertFalse($sut->get('boolProp'));
    }

    public function testItShouldReturnEntityObjectWhenAnInvalidPropertyIsSet()
    {
        $sut = self::makeSut();
        $sut->set('invalidProperty', self::$faker->words(2, true));

        $this->assertInstanceOf(Entity::class, $sut);
    }

    public function testItShouldReturnIsi8601FormatDateWhenPropertyIsInstanceOfDateTimeInterfaceInArrayWay()
    {
        $now = new DateTimeImmutable();
        $sut = self::makeSut();
        $sut->set('dateProp', $now);
        $sut->set('datetimePropUs', $now);
        $sut->set('datetimePropIso', $now);
        $entityAsArray = $sut->toArray();

        $this->assertSame($entityAsArray['dateProp'], $now->format('Y-m-d H:i:s'));
        $this->assertSame($entityAsArray['datetimePropUs'], $now->format('Y-m-d H:i:s'));
        $this->assertSame($entityAsArray['datetimePropIso'], $now->format('Y-m-d H:i:s'));
    }

    public function testItShouldReturnEntityArrayFormatWhenPropertyIsInstanceOfEntity()
    {
        $sut = self::makeSut();
        $entityAsArray = $sut->toArray();
        $childEntityAsArray = $sut->get('childEntity')->toArray();

        $this->assertSame($entityAsArray['childEntity'], $childEntityAsArray);
    }

    public function testItShouldReturnArrayFormatWhenAnObjectIsProvided()
    {
        $sut = self::makeSut();
        $entityAsArray = $sut->toArray();

        $this->assertArrayHasKey('genericObject', $entityAsArray);
        $this->assertArrayHasKey('prop1', $entityAsArray['genericObject']);
        $this->assertArrayHasKey('prop2', $entityAsArray['genericObject']);
        $this->assertArrayHasKey('prop3', $entityAsArray['genericObject']);
    }

    public function testItShouldSetPropertyValuesWhenIsSetFromMagicMethod()
    {
        $text = self::$faker->words(2, true);
        $digit = self::$faker->randomDigit();
        $sut = self::makeSut();

        $sut->stringProp = $text;
        $sut->intProp = $digit;
        $sut->boolProp = false;

        $this->assertSame($sut->stringProp, strtoupper($text));
        $this->assertSame($sut->intProp, $digit);
        $this->assertFalse($sut->boolProp);
    }

    public function testItShouldReturnAnAssociativeArrayWhenEntityIsConvertedToJson()
    {
        $sut = self::makeSut();
        $json = json_encode($sut);
        $jsonToArray = json_decode($json, true);

        $this->assertArrayHasKey('stringProp', $jsonToArray);
        $this->assertArrayHasKey('nullableProp', $jsonToArray);
        $this->assertArrayHasKey('intProp', $jsonToArray);
        $this->assertArrayHasKey('floatProp', $jsonToArray);
        $this->assertArrayHasKey('arrayProp', $jsonToArray);
        $this->assertArrayHasKey('boolProp', $jsonToArray);
        $this->assertArrayHasKey('valueObjectProp', $jsonToArray);
        $this->assertArrayHasKey('dateProp', $jsonToArray);
        $this->assertArrayHasKey('datetimePropUs', $jsonToArray);
        $this->assertArrayHasKey('datetimePropIso', $jsonToArray);
        $this->assertArrayHasKey('childEntity', $jsonToArray);
        $this->assertArrayHasKey('genericObject', $jsonToArray);

        $this->assertNull($jsonToArray['nullableProp']);
        $this->assertIsArray($jsonToArray['arrayProp']);
        $this->assertIsBool($jsonToArray['boolProp']);
        $this->assertSame($jsonToArray['valueObjectProp'], 'any-value-object');
        $this->assertIso8601DateTimeString($jsonToArray['dateProp']);
        $this->assertIso8601DateTimeString($jsonToArray['datetimePropUs']);
        $this->assertIso8601DateTimeString($jsonToArray['datetimePropIso']);
    }
}
