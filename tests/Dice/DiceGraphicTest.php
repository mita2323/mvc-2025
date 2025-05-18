<?php

namespace App\Dice;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Dice.
 */
class DiceGraphicTest extends TestCase
{
    /**
     * Test creating DiceGraphic.
     */
    public function testCreateDiceGraphic(): void
    {
        $dice = new DiceGraphic();
        $this->assertInstanceOf("\App\Dice\DiceGraphic", $dice);
        $this->assertSame(0, $dice->getValue());
        $this->expectException(\OutOfRangeException::class);
        $dice->getAsString();
    }

    /**
     * Test rolling and getting Unicode representation.
     */
    public function testRollAndGetAsString(): void
    {
        $dice = new DiceGraphic();
        $dice->roll();
        $value = $dice->getValue();
        $this->assertGreaterThanOrEqual(1, $value);
        $this->assertLessThanOrEqual(6, $value);

        $expected = ['⚀', '⚁', '⚂', '⚃', '⚄', '⚅'];
        $result = $dice->getAsString();
        $this->assertEquals($expected[$value - 1], $result);
    }

    /**
     * Test getAsString throws exception for invalid value.
     */
    public function testGetAsStringInvalidValue(): void
    {
        $dice = new DiceGraphic();
        $this->assertSame(0, $dice->getValue());
        $dice->roll();
        $value = $dice->getValue();
        $this->assertGreaterThanOrEqual(1, $value);
        $this->assertLessThanOrEqual(6, $value);
    }
}
