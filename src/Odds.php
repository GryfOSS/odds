<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

use Praetorian\Formatter\Odds\Exception\InvalidPriceException;

/**
 * Immutable class representing odds in all supported formats.
 */
final class Odds
{
    private const DECIMAL_PRECISION = 2;
    private const SCALE_FACTOR = 100; // For 2 decimal places

    private readonly string $decimal;
    private readonly string $fractional;
    private readonly string $moneyline;
    private readonly string $probability;

    /**
     * @param string $decimal The decimal odds value as string (e.g., "2.50")
     * @param string $fractional The fractional odds value (e.g., "1/2")
     * @param string $moneyline The moneyline odds value (e.g., "+100" or "-150")
     */
    public function __construct(string $decimal, string $fractional, string $moneyline)
    {
        // Validate decimal format
        if (!is_numeric($decimal) || bccomp($decimal, '1.0', self::DECIMAL_PRECISION) < 0) {
            throw new InvalidPriceException(sprintf('Invalid decimal value provided: %s. Min value: 1.0', $decimal));
        }

        $this->decimal = bcadd($decimal, '0', self::DECIMAL_PRECISION); // Normalize precision
        $this->fractional = $fractional;
        $this->moneyline = $moneyline;
        $this->probability = $this->calculateProbability($this->decimal);
    }

    /**
     * Get the decimal odds value.
     */
    public function getDecimal(): string
    {
        return $this->decimal;
    }

    /**
     * Get the fractional odds value.
     */
    public function getFractional(): string
    {
        return $this->fractional;
    }

    /**
     * Get the moneyline odds value.
     */
    public function getMoneyline(): string
    {
        return $this->moneyline;
    }

    /**
     * Get the calculated probability.
     */
    public function getProbability(): string
    {
        return $this->probability;
    }

    /**
     * Calculate probability from decimal odds.
     */
    private function calculateProbability(string $decimal): string
    {
        if (bccomp($decimal, '0', self::DECIMAL_PRECISION) <= 0) {
            throw new InvalidPriceException('Decimal odds must be greater than 0');
        }

        // probability = 1 / decimal odds
        // Using bcmath for precision: 1 / decimal * 100 for percentage
        return bcmul(bcdiv('1', $decimal, 10), '100', self::DECIMAL_PRECISION);
    }
}
