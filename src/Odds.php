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

    /**
     * @param string $decimal The decimal odds value as string (e.g., "2.50")
     * @param string $fractional The fractional odds value (e.g., "1/2")
     * @param string $moneyline The moneyline odds value (e.g., "+100" or "-150")
     */
    public function __construct(string $decimal, string $fractional, string $moneyline)
    {
        // Validate decimal format
        if (!is_numeric($decimal) || (float)$decimal < 1.0) {
            throw new InvalidPriceException(sprintf('Invalid decimal value provided: %s. Min value: 1.0', $decimal));
        }

        $this->decimal = $this->normalizeDecimal($decimal);
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
     * Calculate the implied probability from decimal odds.
     * Returns as percentage string (e.g., "40.00")
     */
    private function calculateProbability(string $decimal): string
    {
        // Convert to float for precise calculation
        $decimalFloat = (float)$decimal;

        // probability = 1 / decimal odds * 100 (for percentage)
        $probabilityFloat = (1 / $decimalFloat) * 100;

        // Return with 2 decimal places
        return number_format($probabilityFloat, self::DECIMAL_PRECISION, '.', '');
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
        return (int)round((float)$decimal * self::SCALE_FACTOR);
    }

    /**
     * Convert integer back to string decimal (divide by 100).
     * E.g., 250 -> "2.50"
     */
    private function intToString(int $value): string
    {
        return number_format($value / self::SCALE_FACTOR, self::DECIMAL_PRECISION, '.', '');
    }
}
