<?php

namespace App\Dice;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Dice.
 */
class DiceTest extends TestCase
{
    /**
     * Construct object and verify that the object has the expected
     * properties, use no arguments.
     */
    public function testCreateDice(): void
    {
        $die = new Dice();
        $this->assertInstanceOf("\App\Dice\Dice", $die);

        $res = $die->getAsString();
        $this->assertNotEmpty($res);
    }

    /**
     * Test that a roll generates a valid value and updates the dice.
     */
    public function testRoll(): void
    {
        $die = new Dice();
        $result = $die->roll();
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
        $this->assertLessThanOrEqual(6, $result);
        $this->assertSame($result, $die->getValue());
    }

    /**
     * Test getValue before and after rolling.
     */
    public function testGetValues(): void
    {
        $hand = new DiceHand();
        $this->assertEmpty($hand->getValues());

        $dice = new Dice();
        $hand->add($dice);
        $dice->roll();
        $values = $hand->getValues();
        $this->assertCount(1, $values);
    }
}
