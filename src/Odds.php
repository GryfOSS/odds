<?php

declare(strict_types=1);

namespace GryfOSS\Odds;

use GryfOSS\Odds\Exception\InvalidPriceException;

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
    private readonly float $probabilityFloat;

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

        $this->decimal = $this->normalizeDecimal($decimal);
        $this->fractional = $fractional;
        $this->moneyline = $moneyline;
        $this->probability = $this->calculateProbability($this->decimal);
        $this->probabilityFloat = round((float)$this->probability, self::DECIMAL_PRECISION);
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
     * Get the calculated probability as a float.
     * Useful for numerical comparisons.
     */
    public function getProbabilityFloat(): float
    {
        return $this->probabilityFloat;
    }

    /**
     * Calculate the implied probability from decimal odds.
     * Returns as percentage string (e.g., "40.00")
     */
    private function calculateProbability(string $decimal): string
    {
        // probability = 1 / decimal odds * 100 (for percentage)
        // Use higher precision for intermediate calculation and round properly
        $probability = bcdiv('1', $decimal, 6); // High precision for intermediate calculation
        $probabilityPercent = bcmul($probability, '100', 6);

        // Round to 2 decimal places using bcmath
        return $this->bcRound($probabilityPercent, self::DECIMAL_PRECISION);
    }

    /**
     * Normalize decimal string to 2 decimal places.
     */
    private function normalizeDecimal(string $decimal): string
    {
        $decimalInt = $this->stringToInt($decimal);
        return $this->intToString($decimalInt);
    }

    /**
     * Convert string decimal to integer (multiply by 100).
     * E.g., "2.50" -> 250
     */
    private function stringToInt(string $decimal): int
    {
        // Use bcmath to multiply by scale factor and round properly
        $scaled = bcmul($decimal, (string)self::SCALE_FACTOR, 2);
        // Add 0.5 for proper rounding before converting to int
        $rounded = bcadd($scaled, '0.5', 2);
        return (int)bcdiv($rounded, '1', 0);
    }

    /**
     * Convert integer back to string decimal (divide by 100).
     * E.g., 250 -> "2.50"
     */
    private function intToString(int $value): string
    {
        return number_format($value / self::SCALE_FACTOR, self::DECIMAL_PRECISION, '.', '');
    }

    /**
     * Round a bcmath number to specified decimal places.
     */
    private function bcRound(string $number, int $precision): string
    {
        $factor = bcpow('10', (string)$precision, 0);
        $multiplied = bcmul($number, $factor, $precision + 1);

        // Proper rounding for both positive and negative numbers
        if (bccomp($multiplied, '0', $precision + 1) >= 0) {
            $rounded = bcadd($multiplied, '0.5', $precision + 1);
        } else {
            $rounded = bcsub($multiplied, '0.5', $precision + 1);
        }

        $truncated = bcdiv($rounded, '1', 0);
        return bcdiv($truncated, $factor, $precision);
    }
}
