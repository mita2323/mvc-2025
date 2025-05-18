<?php

namespace App\Dice;

use PHPUnit\Framework\TestCase;

/**
 * Test cases for class Dice.
 */
class DiceHandTest extends TestCase
{
    /**
     * Test getting number of dice.
     */
    public function testGetNumberDices(): void
    {
        $hand = new DiceHand();
        $this->assertEquals(0, $hand->getNumberDices());

        $hand->add(new Dice());
        $hand->add(new Dice());
        $this->assertEquals(2, $hand->getNumberDices());
    }

    /**
     * Test rolling dice in the hand.
     */
    public function testRoll(): void
    {
        $hand = new DiceHand();
        $die = new Dice();
        $hand->add($die);
        $hand->roll();

        $value = $die->getValue();
        $this->assertGreaterThanOrEqual(1, $value);
        $this->assertLessThanOrEqual(6, $value);
    }

    /**
     * Test getting string representation of dice.
     * @return void
     */
    public function testGetString()
    {
        $hand = new DiceHand();
        $dice = new Dice();
        $hand->add($dice);
        $hand->roll();

        $strings = $hand->getString();
        $this->assertCount(1, $strings);
        $value = $dice->getValue();
        $this->assertEquals("[$value]", $strings[0]);
    }
}
