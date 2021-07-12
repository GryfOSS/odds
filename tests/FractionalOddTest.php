<?php

declare(strict_types=1);

namespace Praetorian\Tests\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\FractionalOdd;

/**
 * Class FractionalOddTest.
 */
class FractionalOddTest extends TestCase
{
    /**
     * @expectException \InvalidArgumentException
     */
    public function testDenominatorException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FractionalOdd(1, 0);
    }

    /**
     * @expectException \InvalidArgumentException
     */
    public function testNumeratorException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FractionalOdd(-1, 1);
    }

    /**
     * @expectException \InvalidArgumentException
     */
    public function testFractionBarException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FractionalOdd(1, 1, ',');
    }

    public function testValue(): void
    {
        $odd = new FractionalOdd(2, 1);
        $this->assertEquals('2/1', $odd->value());

        $odd = new FractionalOdd(2, 1, '-');
        $this->assertEquals('2-1', $odd->value());
    }

    public function testToDecimal(): void
    {
        $oddFractional = new FractionalOdd(2, 1);
        $oddDecimal = $oddFractional->toDecimal();
        $this->assertEquals(3.0, $oddDecimal->value());
    }

    public function testToFractional(): void
    {
        $odd = new FractionalOdd(2, 1);
        $odd2 = $odd->toFractional();
        $this->assertEquals('2/1', $odd2->value());
    }

    public function testToMoneyline(): void
    {
        $oddFractional = new FractionalOdd(0, 1);
        $oddMoneyline = $oddFractional->toMoneyline();
        $this->assertEquals('0', $oddMoneyline->value());

        $oddFractional = new FractionalOdd(1, 2);
        $oddMoneyline = $oddFractional->toMoneyline();
        $this->assertEquals('-200', $oddMoneyline->value());

        $oddFractional = new FractionalOdd(2, 1);
        $oddMoneyline = $oddFractional->toMoneyline();
        $this->assertEquals('+200', $oddMoneyline->value());
    }
}
