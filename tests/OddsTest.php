<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Odds;

use PHPUnit\Framework\TestCase;
use GryfOSS\Odds\Odds;
use GryfOSS\Odds\Exception\InvalidPriceException;

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

    public function testDecimalNormalization(): void
    {
        // Test that decimal values are normalized to 2 decimal places
        $odds = new Odds('2', '1/1', '+100');
        $this->assertEquals('2.00', $odds->getDecimal());

        $odds = new Odds('2.5', '3/2', '+150');
        $this->assertEquals('2.50', $odds->getDecimal());

        $odds = new Odds('1.123', '1/8', '-800');
        $this->assertEquals('1.12', $odds->getDecimal());
    }

    public function testInvalidDecimalNonNumeric(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid decimal value provided: abc. Min value: 1.0');

        new Odds('abc', '1/1', '+100');
    }

    public function testInvalidDecimalZero(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid decimal value provided: 0. Min value: 1.0');

        new Odds('0', '1/1', '+100');
    }

    public function testInvalidDecimalNegative(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid decimal value provided: -1.5. Min value: 1.0');

        new Odds('-1.5', '1/1', '+100');
    }

    public function testMinimumValidDecimal(): void
    {
        $odds = new Odds('1.0', '0/1', '0');
        $this->assertEquals('1.00', $odds->getDecimal());
        $this->assertEquals('100.00', $odds->getProbability());
    }

    public function testHighDecimalOdds(): void
    {
        $odds = new Odds('100.0', '99/1', '+9900');
        $this->assertEquals('100.00', $odds->getDecimal());
        $this->assertEquals('1.00', $odds->getProbability());
    }

    public function testProbabilityCalculationEdgeCases(): void
    {
        // Test probability calculation for edge cases
        $odds = new Odds('1.01', '1/100', '-10000');
        $this->assertEquals('99.01', $odds->getProbability());

        $odds = new Odds('10.00', '9/1', '+900');
        $this->assertEquals('10.00', $odds->getProbability());

        $odds = new Odds('1.5', '1/2', '-200');
        $this->assertEquals('66.67', $odds->getProbability());
    }

    public function testDecimalRounding(): void
    {
        // Test that decimal values are properly rounded
        $odds = new Odds('2.005', '1/1', '+100');
        $this->assertEquals('2.01', $odds->getDecimal()); // Should round up

        $odds = new Odds('2.004', '1/1', '+100');
        $this->assertEquals('2.00', $odds->getDecimal()); // Should round down
    }

    public function testAllGettersReturnStrings(): void
    {
        $odds = new Odds('2.00', '1/1', '+100');

        $this->assertIsString($odds->getDecimal());
        $this->assertIsString($odds->getFractional());
        $this->assertIsString($odds->getMoneyline());
        $this->assertIsString($odds->getProbability());
    }
}
