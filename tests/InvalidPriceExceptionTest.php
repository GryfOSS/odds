<?php

declare(strict_types=1);

namespace GryfOSS\Tests\Odds;

use PHPUnit\Framework\TestCase;
use GryfOSS\Odds\Exception\InvalidPriceException;

/**
 * Class InvalidPriceExceptionTest.
 */
class InvalidPriceExceptionTest extends TestCase
{
    public function testExceptionIsInstanceOfInvalidArgumentException(): void
    {
        $exception = new InvalidPriceException('Test message');

        $this->assertInstanceOf(\InvalidArgumentException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testExceptionMessage(): void
    {
        $message = 'Invalid price provided';
        $exception = new InvalidPriceException($message);

        $this->assertEquals($message, $exception->getMessage());
    }

    public function testExceptionCode(): void
    {
        $code = 100;
        $exception = new InvalidPriceException('Test message', $code);

        $this->assertEquals($code, $exception->getCode());
    }

    public function testExceptionWithPreviousException(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new InvalidPriceException('Test message', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testCanBeThrown(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Test exception message');

        throw new InvalidPriceException('Test exception message');
    }

    public function testCanBeCaught(): void
    {
        try {
            throw new InvalidPriceException('Caught exception');
        } catch (InvalidPriceException $e) {
            $this->assertEquals('Caught exception', $e->getMessage());
            $this->assertTrue(true); // Test passes if we reach this point
        }
    }
}
