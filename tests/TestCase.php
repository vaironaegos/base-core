<?php

namespace Astrotech\Core\Base\Tests;

use Faker\Factory as Faker;
use Faker\Generator;
use PHPUnit\Framework\TestCase as PHPUnitTestCast;

abstract class TestCase extends PHPUnitTestCast
{
    use Asserts;

    protected static Generator $faker;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        self::$faker = Faker::create('pt_BR');
    }
}
