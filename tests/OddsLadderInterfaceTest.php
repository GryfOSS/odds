<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Odds;

use PHPUnit\Framework\TestCase;
use GryfOSS\Odds\OddsLadderInterface;

/**
 * Class OddsLadderInterfaceTest.
 */
class OddsLadderInterfaceTest extends TestCase
{
    public function testInterfaceExists(): void
    {
        $this->assertTrue(interface_exists(OddsLadderInterface::class));
    }

    public function testInterfaceHasDecimalToFractionalMethod(): void
    {
        $reflection = new \ReflectionClass(OddsLadderInterface::class);
        $this->assertTrue($reflection->hasMethod('decimalToFractional'));

        $method = $reflection->getMethod('decimalToFractional');
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isAbstract());

        // Check method signature
        $parameters = $method->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertEquals('decimal', $parameters[0]->getName());
        $this->assertTrue($parameters[0]->hasType());
        $this->assertEquals('string', $parameters[0]->getType()->getName());
    }

    public function testInterfaceCanBeImplemented(): void
    {
        // Create an anonymous implementation to test the interface
        $implementation = new class implements OddsLadderInterface {
            public function decimalToFractional(string $decimal): string
            {
                return '1/1'; // Simple implementation for testing
            }
        };

        $this->assertInstanceOf(OddsLadderInterface::class, $implementation);
        $this->assertEquals('1/1', $implementation->decimalToFractional('2.00'));
    }

    public function testConcreteClassesImplementInterface(): void
    {
        // Verify that concrete classes implement the interface
        $standardLadder = new \GryfOSS\Odds\OddsLadder();
        $this->assertInstanceOf(OddsLadderInterface::class, $standardLadder);

        $customLadder = new \GryfOSS\Odds\CustomOddsLadder();
        $this->assertInstanceOf(OddsLadderInterface::class, $customLadder);
    }
}
