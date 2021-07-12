<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\DecimalOdd;

/**
 * Class DecimalOddTest.
 */
class DecimalOddTest extends TestCase
{
    /**
     * @expectException \InvalidArgumentException
     */
    public function testInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DecimalOdd(0.0);
    }

    public function testValue(): void
    {
        $odd = new DecimalOdd(1.5);
        $this->assertEquals(1.5, $odd->value());
    }

    public function testToDecimal(): void
    {
        $odd = new DecimalOdd(1.5);
        $odd2 = $odd->toDecimal();
        $this->assertEquals(1.5, $odd2->value());
    }

    public function testToFractional(): void
    {
        $oddDecimal = new DecimalOdd(1.0);
        $oddFractional = $oddDecimal->toFractional();
        $this->assertEquals('0/1', $oddFractional->value());

        $oddDecimal = new DecimalOdd(1.5);
        $oddFractional = $oddDecimal->toFractional();
        $this->assertEquals('1/2', $oddFractional->value());

        $oddDecimal = new DecimalOdd(3.4);
        $oddFractional = $oddDecimal->toFractional();
        $this->assertEquals('12/5', $oddFractional->value());
    }

    public function testToMoneyline(): void
    {
        $oddDecimal = new DecimalOdd(1.0);
        $oddMoneyline = $oddDecimal->toMoneyline();
        $this->assertEquals('0', $oddMoneyline->value());

        $oddDecimal = new DecimalOdd(2.0);
        $oddMoneyline = $oddDecimal->toMoneyline();
        $this->assertEquals('+100', $oddMoneyline->value());

        $oddDecimal = new DecimalOdd(2.6);
        $oddMoneyline = $oddDecimal->toMoneyline();
        $this->assertEquals('+160', $oddMoneyline->value());

        $oddDecimal = new DecimalOdd(1.6);
        $oddMoneyline = $oddDecimal->toMoneyline();
        $this->assertEquals('-166.67', $oddMoneyline->value());
    }
}
