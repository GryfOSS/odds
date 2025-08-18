<?php

declare(strict_types=1);

namespace Praetorian\Tests\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\Utils\OddsLadder;

/**
 * Class UtilsOddsLadderTest.
 */
class UtilsOddsLadderTest extends TestCase
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
        $this->assertEquals('1/33', $this->oddsLadder->decimalToFractional('1.03'));
        $this->assertEquals('1/25', $this->oddsLadder->decimalToFractional('1.04'));
        $this->assertEquals('1/20', $this->oddsLadder->decimalToFractional('1.05'));
        $this->assertEquals('1/2', $this->oddsLadder->decimalToFractional('1.50'));
        $this->assertEquals('1/1', $this->oddsLadder->decimalToFractional('2.00'));
        $this->assertEquals('9/1', $this->oddsLadder->decimalToFractional('10.00'));
    }

    public function testDecimalToFractionalBelowFirstThreshold(): void
    {
        // Test values below the first threshold (should use first threshold)
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.01'));
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.015'));
    }

    public function testDecimalToFractionalAtExactThresholds(): void
    {
        // Test exact threshold values from string-based ladder
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.02'));
        $this->assertEquals('1/10', $this->oddsLadder->decimalToFractional('1.10'));
        $this->assertEquals('1/4', $this->oddsLadder->decimalToFractional('1.25'));
        $this->assertEquals('1/3', $this->oddsLadder->decimalToFractional('1.33'));
        $this->assertEquals('3/2', $this->oddsLadder->decimalToFractional('2.50'));
        $this->assertEquals('3/1', $this->oddsLadder->decimalToFractional('4.00'));
    }

    public function testDecimalToFractionalBetweenThresholds(): void
    {
        // Test values between thresholds
        $this->assertEquals('1/50', $this->oddsLadder->decimalToFractional('1.025'));
        $this->assertEquals('1/9', $this->oddsLadder->decimalToFractional('1.105'));
        $this->assertEquals('1/3', $this->oddsLadder->decimalToFractional('1.30'));
        $this->assertEquals('1/2', $this->oddsLadder->decimalToFractional('1.45'));
        $this->assertEquals('1/1', $this->oddsLadder->decimalToFractional('1.95'));
    }

    public function testDecimalToFractionalFallback(): void
    {
        // Test values that exceed the ladder (should use fallback conversion)
        $this->assertEquals('10/1', $this->oddsLadder->decimalToFractional('11.00'));
        $this->assertEquals('14/1', $this->oddsLadder->decimalToFractional('15.00'));
        $this->assertEquals('19/1', $this->oddsLadder->decimalToFractional('20.00'));
        $this->assertEquals('49/1', $this->oddsLadder->decimalToFractional('50.00'));
        $this->assertEquals('99/1', $this->oddsLadder->decimalToFractional('100.00'));
    }

    public function testGetLadderStructure(): void
    {
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->oddsLadder);
        $getLadderMethod = $reflection->getMethod('getLadder');
        $getLadderMethod->setAccessible(true);

        $ladder = $getLadderMethod->invoke($this->oddsLadder);

        $this->assertIsArray($ladder);
        $this->assertNotEmpty($ladder);

        // Test some key entries (string keys)
        $this->assertEquals('1/50', $ladder['1.02']);
        $this->assertEquals('1/2', $ladder['1.50']);
        $this->assertEquals('1/1', $ladder['2.00']);
        $this->assertEquals('9/1', $ladder['10.00']);

        // Verify it's a comprehensive ladder
        $this->assertGreaterThan(30, count($ladder));
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

    public function testFallbackConversion(): void
    {
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->oddsLadder);
        $fallbackMethod = $reflection->getMethod('fallbackConversion');
        $fallbackMethod->setAccessible(true);

        $this->assertEquals('10/1', $fallbackMethod->invoke($this->oddsLadder, '11.00'));
        $this->assertEquals('19/1', $fallbackMethod->invoke($this->oddsLadder, '20.00'));
        $this->assertEquals('49/1', $fallbackMethod->invoke($this->oddsLadder, '50.00'));
        $this->assertEquals('99/1', $fallbackMethod->invoke($this->oddsLadder, '100.00'));
    }

    public function testImplementsInterface(): void
    {
        // Verify it implements the OddsLadderInterface
        $this->assertInstanceOf(\Praetorian\Formatter\Odds\OddsLadderInterface::class, $this->oddsLadder);
    }

    public function testLadderIteration(): void
    {
        // Test that ladder iteration works correctly with string-to-int conversion
        $testCases = [
            ['1.01', '1/50'],  // Below first threshold
            ['1.02', '1/50'],  // At first threshold
            ['1.021', '1/50'], // Just above first threshold but still below 1.03
            ['1.10', '1/10'],  // At specific threshold
            ['1.101', '1/10'], // Just above threshold but still below 1.11
            ['2.00', '1/1'],   // At evens
            ['2.01', '11/10'], // Just above evens
        ];

        foreach ($testCases as [$decimal, $expected]) {
            $this->assertEquals($expected, $this->oddsLadder->decimalToFractional($decimal),
                "Failed for decimal: $decimal");
        }
    }
}
