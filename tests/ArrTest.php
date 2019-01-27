<?php 

namespace PaladinsDev\PHP\Tests;

use PHPUnit\Framework\TestCase;
use PaladinsDev\PHP\Arr;

class ArrTest extends TestCase
{
    public function testFirst()
    {
        $testArray = [100, 150, 200, 250, 300];


        $this->assertEquals(150, Arr::first($testArray, function ($value, $key) {
            return $value === 150;
        }));
    }
}