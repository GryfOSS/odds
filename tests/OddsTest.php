<?php

declare(strict_types=1);

namespace Praetorian\Tests\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\Odds;
use Praetorian\Formatter\Odds\Exception\InvalidPriceException;

/**
 * Class OddsTest.
 */
class OddsTest extends TestCase
{
    public function testInvalidDecimalException(): void
    {
        $this->expectException(InvalidPriceException::class);
        new Odds('0.5', '1/2', '+100');
    }

    public function testValidConstruction(): void
    {
        $odds = new Odds('2.00', '1/1', '+100');

        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional());
        $this->assertEquals('+100', $odds->getMoneyline());
        $this->assertEquals('50.00', $odds->getProbability());
    }

    public function testProbabilityCalculation(): void
    {
        $odds = new Odds('2.00', '1/1', '+100');
        $this->assertEquals('50.00', $odds->getProbability());

        $odds = new Odds('4.00', '3/1', '+300');
        $this->assertEquals('25.00', $odds->getProbability());
    }

    public function testImmutability(): void
    {
        $odds = new Odds('2.00', '1/1', '+100');

        // Verify that getters return the same values consistently
        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional());
        $this->assertEquals('1/1', $odds->getFractional());
    }
}
