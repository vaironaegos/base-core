<?php

declare(strict_types=1);

namespace Astrotech\Core\Base\Tests;

use Faker\Generator;
use Faker\Factory as Faker;
use PHPUnit\Framework\TestCase as PHPUnitTestCast;

abstract class TestCase extends PHPUnitTestCast
{
    use Asserts;

    protected static Generator $faker;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        self::$faker = Faker::create('pt_BR');
    }
}
