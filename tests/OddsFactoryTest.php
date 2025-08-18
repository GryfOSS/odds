<?php

declare(strict_types=1);

namespace Praetorian\Tests\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\OddsFactory;
use Praetorian\Formatter\Odds\Utils\OddsLadder;
use Praetorian\Formatter\Odds\CustomOddsLadder;
use Praetorian\Formatter\Odds\Exception\InvalidPriceException;

/**
 * Class OddsFactoryTest.
 */
class OddsFactoryTest extends TestCase
{
    private OddsFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new OddsFactory();
    }

    public function testFromDecimal(): void
    {
        $odds = $this->factory->fromDecimal('2.00');

        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional());
        $this->assertEquals('+100', $odds->getMoneyline());
        $this->assertEquals('50.00', $odds->getProbability());
    }

    public function testFromDecimalWithOddsLadder(): void
    {
        $odds = $this->factory->fromDecimal('1.50');

        $this->assertEquals('1.50', $odds->getDecimal());
        $this->assertEquals('1/2', $odds->getFractional());
        $this->assertEquals('-200', $odds->getMoneyline());
        $this->assertEquals('66.67', $odds->getProbability());
    }

    public function testFromDecimalInvalid(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->factory->fromDecimal('0.5');
    }

    public function testFromFractional(): void
    {
        $odds = $this->factory->fromFractional(1, 1);

        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional());
        $this->assertEquals('+100', $odds->getMoneyline());
        $this->assertEquals('50.00', $odds->getProbability());
    }

    public function testFromFractionalEvens(): void
    {
        $odds = $this->factory->fromFractional(1, 2);

        $this->assertEquals('1.50', $odds->getDecimal());
        $this->assertEquals('1/2', $odds->getFractional());
        $this->assertEquals('-200', $odds->getMoneyline());
        $this->assertEquals('66.67', $odds->getProbability());
    }

    public function testFromFractionalInvalidNumerator(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->factory->fromFractional(-1, 2);
    }

    public function testFromFractionalInvalidDenominator(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->factory->fromFractional(1, 0);
    }

    public function testFromMoneyline(): void
    {
        $odds = $this->factory->fromMoneyline('100');

        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional());
        $this->assertEquals('+100', $odds->getMoneyline());
        $this->assertEquals('50.00', $odds->getProbability());
    }

    public function testFromMoneylineNegative(): void
    {
        $odds = $this->factory->fromMoneyline('-200');

        $this->assertEquals('1.50', $odds->getDecimal());
        $this->assertEquals('1/2', $odds->getFractional());
        $this->assertEquals('-200', $odds->getMoneyline());
        $this->assertEquals('66.67', $odds->getProbability());
    }

    public function testFromMoneylineEven(): void
    {
        $odds = $this->factory->fromMoneyline('0');

        $this->assertEquals('1.00', $odds->getDecimal());
        $this->assertEquals('0/1', $odds->getFractional());
        $this->assertEquals('0', $odds->getMoneyline());
        $this->assertEquals('100.00', $odds->getProbability());
    }

    public function testWithCustomOddsLadder(): void
    {
        $oddsLadder = new OddsLadder();
        $factory = new OddsFactory($oddsLadder);

        $odds = $factory->fromDecimal('2.00');

        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional()); // From odds ladder
        $this->assertEquals('+100', $odds->getMoneyline());
        $this->assertEquals('50.00', $odds->getProbability());
    }

    public function testDefaultConversionWithoutOddsLadder(): void
    {
        $factory = new OddsFactory(); // No odds ladder injected (default)

        $odds = $factory->fromDecimal('2.00');

        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional()); // Default mathematical conversion
        $this->assertEquals('+100', $odds->getMoneyline());
        $this->assertEquals('50.00', $odds->getProbability());
    }

    public function testWithCustomOddsLadderExtension(): void
    {
        $customLadder = new CustomOddsLadder();
        $factory = new OddsFactory($customLadder);

        $odds = $factory->fromDecimal('1.90'); // This should find threshold 2.0 and return '1/1'

        $this->assertEquals('1.90', $odds->getDecimal());
        $this->assertEquals('1/1', $odds->getFractional()); // From custom ladder (1.90 <= 2.0 -> '1/1')
        $this->assertEquals('-111.11', $odds->getMoneyline());
        $this->assertEquals('52.63', $odds->getProbability());
    }

    public function testFromDecimalEdgeCases(): void
    {
        // Test minimum valid decimal
        $odds = $this->factory->fromDecimal('1.00');
        $this->assertEquals('1.00', $odds->getDecimal());
        $this->assertEquals('0/1', $odds->getFractional());
        $this->assertEquals('0', $odds->getMoneyline());
        $this->assertEquals('100.00', $odds->getProbability());

        // Test decimal with many digits (should be rounded)
        $odds = $this->factory->fromDecimal('2.005');
        $this->assertEquals('2.01', $odds->getDecimal());
    }

    public function testFromDecimalNonNumeric(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid decimal value provided: abc. Min value: 1.0');

        $this->factory->fromDecimal('abc');
    }

    public function testFromDecimalNegative(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid decimal value provided: -2.0. Min value: 1.0');

        $this->factory->fromDecimal('-2.0');
    }

    public function testFromFractionalZeroNumerator(): void
    {
        $odds = $this->factory->fromFractional(0, 1);
        $this->assertEquals('1.00', $odds->getDecimal());
        $this->assertEquals('0/1', $odds->getFractional());
        $this->assertEquals('0', $odds->getMoneyline());
        $this->assertEquals('100.00', $odds->getProbability());
    }

    public function testFromFractionalLargeNumbers(): void
    {
        $odds = $this->factory->fromFractional(100, 1);
        $this->assertEquals('101.00', $odds->getDecimal());
        $this->assertEquals('100/1', $odds->getFractional());
        $this->assertEquals('+10000', $odds->getMoneyline());
    }

    public function testFromFractionalInvalidNegativeDenominator(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid denominator provided');

        $this->factory->fromFractional(1, -1);
    }

    public function testFromMoneylineNonNumeric(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid moneyline value provided: abc');

        $this->factory->fromMoneyline('abc');
    }

    public function testFromMoneylinePositiveWithoutSign(): void
    {
        $odds = $this->factory->fromMoneyline('100');
        $this->assertEquals('2.00', $odds->getDecimal());
        $this->assertEquals('+100', $odds->getMoneyline());
    }

    public function testFromMoneylinePositiveWithSign(): void
    {
        $odds = $this->factory->fromMoneyline('+150');
        $this->assertEquals('2.50', $odds->getDecimal());
        $this->assertEquals('+150', $odds->getMoneyline());
    }

    public function testFromMoneylineVeryHighPositive(): void
    {
        $odds = $this->factory->fromMoneyline('10000');
        $this->assertEquals('101.00', $odds->getDecimal());
        $this->assertEquals('+10000', $odds->getMoneyline());
    }

    public function testFromMoneylineVeryHighNegative(): void
    {
        $odds = $this->factory->fromMoneyline('-10000');
        $this->assertEquals('1.01', $odds->getDecimal());
        $this->assertEquals('-10000', $odds->getMoneyline());
    }

    public function testFromMoneylineDecimalValues(): void
    {
        $odds = $this->factory->fromMoneyline('150.50');
        $this->assertEquals('2.51', $odds->getDecimal());
        $this->assertEquals('+150.50', $odds->getMoneyline());
    }

    public function testDefaultConversionComplexFractional(): void
    {
        // Test default fractional conversion with tolerance
        $factory = new OddsFactory(); // No odds ladder

        $odds = $factory->fromDecimal('3.33');
        $this->assertEquals('3.33', $odds->getDecimal());
        // Should use continued fractions algorithm
        $this->assertNotEmpty($odds->getFractional());
    }

    public function testMoneylineFormatting(): void
    {
        // Test moneyline formatting for whole numbers
        $odds = $this->factory->fromDecimal('3.00');
        $this->assertEquals('+200', $odds->getMoneyline()); // Should not have decimals

        $odds = $this->factory->fromDecimal('1.50');
        $this->assertEquals('-200', $odds->getMoneyline()); // Should not have decimals
    }

    public function testDefaultFractionalConversionEdgeCases(): void
    {
        $factory = new OddsFactory(); // No odds ladder

        // Test continued fractions algorithm edge cases
        $odds = $factory->fromDecimal('1.33');
        $this->assertEquals('33/100', $odds->getFractional());

        $odds = $factory->fromDecimal('1.67');
        $this->assertEquals('67/100', $odds->getFractional());

        $odds = $factory->fromDecimal('2.33');
        $this->assertEquals('133/100', $odds->getFractional());
    }
}
