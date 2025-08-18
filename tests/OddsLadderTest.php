<?php

declare(strict_types=1);

namespace Praetorian\Tests\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\OddsLadder;

/**
 * Class OddsLadderTest.
 */
class OddsLadderTest extends TestCase
{
    private OddsLadder $oddsLadder;

    protected function setUp(): void
    {
        $this->oddsLadder = new OddsLadder();
    }

    public function testDecimalToFractionalWithinLadder(): void
    {
        // Test various decimal values that should map to ladder values
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.02'));
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.01')); // Should use 1.02 threshold
        $this->assertEquals('1/33', $this->oddsLadder->decimalToFractional('1.03'));
        $this->assertEquals('1/25', $this->oddsLadder->decimalToFractional('1.04'));
        $this->assertEquals('1/2', $this->oddsLadder->decimalToFractional('1.50'));
        $this->assertEquals('1/1', $this->oddsLadder->decimalToFractional('2.00'));
        $this->assertEquals('9/1', $this->oddsLadder->decimalToFractional('10.00'));
    }

    public function testDecimalToFractionalAtThresholds(): void
    {
        // Test exact threshold values
        $this->assertEquals('1/20', $this->oddsLadder->decimalToFractional('1.05'));
        $this->assertEquals('1/10', $this->oddsLadder->decimalToFractional('1.10'));
        $this->assertEquals('1/4', $this->oddsLadder->decimalToFractional('1.25'));
        $this->assertEquals('3/2', $this->oddsLadder->decimalToFractional('2.50'));
        $this->assertEquals('3/1', $this->oddsLadder->decimalToFractional('4.00'));
    }

    public function testDecimalToFractionalBelowThresholds(): void
    {
        // Test values just below thresholds
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.019'));
        $this->assertEquals('1/25', $this->oddsLadder->decimalToFractional('1.039'));
        $this->assertEquals('1/2', $this->oddsLadder->decimalToFractional('1.49'));
        $this->assertEquals('1/1', $this->oddsLadder->decimalToFractional('1.99'));
    }

    public function testDecimalToFractionalFallback(): void
    {
        // Test values that exceed the ladder (should use fallback conversion)
        $this->assertEquals('10/1', $this->oddsLadder->decimalToFractional('11.00'));
        $this->assertEquals('19/1', $this->oddsLadder->decimalToFractional('20.00'));
        $this->assertEquals('49/1', $this->oddsLadder->decimalToFractional('50.00'));
        $this->assertEquals('99/1', $this->oddsLadder->decimalToFractional('100.00'));
    }

    public function testDecimalToFractionalWithDecimalValues(): void
    {
        // Test non-integer fallback values
        $this->assertEquals('10/1', $this->oddsLadder->decimalToFractional('11.50')); // 10.5 -> 10/1
        $this->assertEquals('14/1', $this->oddsLadder->decimalToFractional('15.25')); // 14.25 -> 14/1
    }

    public function testStringToIntConversion(): void
    {
        // Use reflection to test protected methods
        $reflection = new \ReflectionClass($this->oddsLadder);
        $stringToIntMethod = $reflection->getMethod('stringToInt');
        $stringToIntMethod->setAccessible(true);

        $this->assertEquals(100, $stringToIntMethod->invoke($this->oddsLadder, '1.00'));
        $this->assertEquals(150, $stringToIntMethod->invoke($this->oddsLadder, '1.50'));
        $this->assertEquals(200, $stringToIntMethod->invoke($this->oddsLadder, '2.00'));
        $this->assertEquals(250, $stringToIntMethod->invoke($this->oddsLadder, '2.50'));
        $this->assertEquals(1000, $stringToIntMethod->invoke($this->oddsLadder, '10.00'));
    }

    public function testIntToStringConversion(): void
    {
        // Use reflection to test protected methods
        $reflection = new \ReflectionClass($this->oddsLadder);
        $intToStringMethod = $reflection->getMethod('intToString');
        $intToStringMethod->setAccessible(true);

        $this->assertEquals('1.00', $intToStringMethod->invoke($this->oddsLadder, 100));
        $this->assertEquals('1.50', $intToStringMethod->invoke($this->oddsLadder, 150));
        $this->assertEquals('2.00', $intToStringMethod->invoke($this->oddsLadder, 200));
        $this->assertEquals('2.50', $intToStringMethod->invoke($this->oddsLadder, 250));
        $this->assertEquals('10.00', $intToStringMethod->invoke($this->oddsLadder, 1000));
    }

    public function testGetLadder(): void
    {
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->oddsLadder);
        $getLadderMethod = $reflection->getMethod('getLadder');
        $getLadderMethod->setAccessible(true);

        $ladder = $getLadderMethod->invoke($this->oddsLadder);

        $this->assertIsArray($ladder);
        $this->assertNotEmpty($ladder);

        // Test some key entries
        $this->assertEquals('1/50', $ladder[102]);
        $this->assertEquals('1/2', $ladder[150]);
        $this->assertEquals('1/1', $ladder[200]);
        $this->assertEquals('9/1', $ladder[1000]);
    }

    public function testFallbackConversion(): void
    {
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->oddsLadder);
        $fallbackMethod = $reflection->getMethod('fallbackConversion');
        $fallbackMethod->setAccessible(true);

        $this->assertEquals('10/1', $fallbackMethod->invoke($this->oddsLadder, '11.00'));
        $this->assertEquals('19/1', $fallbackMethod->invoke($this->oddsLadder, '20.00'));
        $this->assertEquals('49/1', $fallbackMethod->invoke($this->oddsLadder, '50.00'));
    }
}
