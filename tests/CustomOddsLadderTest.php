<?php

declare(strict_types=1);

namespace Praetorian\Tests\Formatter\Odds\Tests;

use PHPUnit\Framework\TestCase;
use Praetorian\Formatter\Odds\CustomOddsLadder;

/**
 * Class CustomOddsLadderTest.
 */
class CustomOddsLadderTest extends TestCase
{
    private CustomOddsLadder $customOddsLadder;

    protected function setUp(): void
    {
        $this->customOddsLadder = new CustomOddsLadder();
    }

    public function testDecimalToFractionalWithCustomLadder(): void
    {
        // Test values that should map to custom ladder values
        $this->assertEquals('1/5', $this->customOddsLadder->decimalToFractional('1.20'));
        $this->assertEquals('1/5', $this->customOddsLadder->decimalToFractional('1.15')); // Should use 1.20 threshold
        $this->assertEquals('1/4', $this->customOddsLadder->decimalToFractional('1.25'));
        $this->assertEquals('1/3', $this->customOddsLadder->decimalToFractional('1.33'));
        $this->assertEquals('1/2', $this->customOddsLadder->decimalToFractional('1.50'));
        $this->assertEquals('1/1', $this->customOddsLadder->decimalToFractional('2.00'));
        $this->assertEquals('3/2', $this->customOddsLadder->decimalToFractional('2.50'));
        $this->assertEquals('2/1', $this->customOddsLadder->decimalToFractional('3.00'));
        $this->assertEquals('3/1', $this->customOddsLadder->decimalToFractional('4.00'));
        $this->assertEquals('4/1', $this->customOddsLadder->decimalToFractional('5.00'));
        $this->assertEquals('5/1', $this->customOddsLadder->decimalToFractional('6.00'));
    }

    public function testDecimalToFractionalBelowFirstThreshold(): void
    {
        // Test values below the first threshold in custom ladder
        $this->assertEquals('1/5', $this->customOddsLadder->decimalToFractional('1.10'));
        $this->assertEquals('1/5', $this->customOddsLadder->decimalToFractional('1.05'));
        $this->assertEquals('1/5', $this->customOddsLadder->decimalToFractional('1.01'));
    }

    public function testDecimalToFractionalAtExactThresholds(): void
    {
        // Test exact threshold values
        $this->assertEquals('1/5', $this->customOddsLadder->decimalToFractional('1.20'));
        $this->assertEquals('1/4', $this->customOddsLadder->decimalToFractional('1.25'));
        $this->assertEquals('1/3', $this->customOddsLadder->decimalToFractional('1.33'));
        $this->assertEquals('1/2', $this->customOddsLadder->decimalToFractional('1.50'));
        $this->assertEquals('1/1', $this->customOddsLadder->decimalToFractional('2.00'));
    }

    public function testDecimalToFractionalBetweenThresholds(): void
    {
        // Test values between thresholds
        $this->assertEquals('1/4', $this->customOddsLadder->decimalToFractional('1.24'));
        $this->assertEquals('1/3', $this->customOddsLadder->decimalToFractional('1.32'));
        $this->assertEquals('1/2', $this->customOddsLadder->decimalToFractional('1.45'));
        $this->assertEquals('1/1', $this->customOddsLadder->decimalToFractional('1.90'));
        $this->assertEquals('3/2', $this->customOddsLadder->decimalToFractional('2.40'));
    }

    public function testDecimalToFractionalFallbackForHighOdds(): void
    {
        // Test values that exceed the custom ladder (should use fallback conversion)
        $this->assertEquals('6/1', $this->customOddsLadder->decimalToFractional('7.00'));
        $this->assertEquals('9/1', $this->customOddsLadder->decimalToFractional('10.00'));
        $this->assertEquals('19/1', $this->customOddsLadder->decimalToFractional('20.00'));
        $this->assertEquals('49/1', $this->customOddsLadder->decimalToFractional('50.00'));
    }

    public function testCustomLadderStructure(): void
    {
        // Use reflection to test the custom ladder structure
        $reflection = new \ReflectionClass($this->customOddsLadder);
        $getLadderMethod = $reflection->getMethod('getLadder');
        $getLadderMethod->setAccessible(true);

        $ladder = $getLadderMethod->invoke($this->customOddsLadder);

        $this->assertIsArray($ladder);
        $this->assertNotEmpty($ladder);

        // Test specific custom ladder entries
        $this->assertEquals('1/5', $ladder[120]);
        $this->assertEquals('1/4', $ladder[125]);
        $this->assertEquals('1/3', $ladder[133]);
        $this->assertEquals('1/2', $ladder[150]);
        $this->assertEquals('1/1', $ladder[200]);
        $this->assertEquals('3/2', $ladder[250]);
        $this->assertEquals('2/1', $ladder[300]);
        $this->assertEquals('3/1', $ladder[400]);
        $this->assertEquals('4/1', $ladder[500]);
        $this->assertEquals('5/1', $ladder[600]);

        // Verify the custom ladder is smaller than the standard one
        $this->assertCount(10, $ladder);
    }

    public function testInheritanceFromOddsLadder(): void
    {
        // Verify it inherits from OddsLadder
        $this->assertInstanceOf(\Praetorian\Formatter\Odds\OddsLadder::class, $this->customOddsLadder);
        $this->assertInstanceOf(\Praetorian\Formatter\Odds\OddsLadderInterface::class, $this->customOddsLadder);
    }

    public function testStringIntConversionsInherited(): void
    {
        // Test that inherited protected methods work correctly
        $reflection = new \ReflectionClass($this->customOddsLadder);

        $stringToIntMethod = $reflection->getMethod('stringToInt');
        $stringToIntMethod->setAccessible(true);
        $this->assertEquals(200, $stringToIntMethod->invoke($this->customOddsLadder, '2.00'));

        $intToStringMethod = $reflection->getMethod('intToString');
        $intToStringMethod->setAccessible(true);
        $this->assertEquals('2.00', $intToStringMethod->invoke($this->customOddsLadder, 200));
    }
}
