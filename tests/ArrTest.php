<?php 

namespace PaladinsDev\PHP\Tests;

use PHPUnit\Framework\TestCase;
use PaladinsDev\PHP\Arr;

class ArrTest extends TestCase
{
    public function testFirst()
    {
        $array = [100, 200, 300];

        $value = Arr::first($array, function ($value) {
            return $value >= 150;
        });

        $this->assertEquals(200, $value);
        $this->assertEquals(100, Arr::first($array));
    }

    public function testFirstDefault()
    {
        $array = [100, 200, 300];

        $value = Arr::first($array, function ($value) {
            return $value == 400;
        });

        $this->assertNull($value);
    }

    public function testFirstEmptyArray()
    {
        $this->assertNull(Arr::first([], null));
    }
}