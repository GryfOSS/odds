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

        $odds = $factory->fromDecimal('1.90'); // This should find threshold 2.0 and return 'evens'

        $this->assertEquals('1.90', $odds->getDecimal());
        $this->assertEquals('evens', $odds->getFractional()); // From custom ladder (1.90 <= 2.0 -> 'evens')
        $this->assertEquals('-111.11', $odds->getMoneyline());
        $this->assertEquals('52.63', $odds->getProbability());
    }
}
