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

    private readonly float $decimal;
    private readonly string $fractional;
    private readonly string $moneyline;
    private readonly float $probability;

    /**
     * @param float $decimal The decimal odds value
     * @param string $fractional The fractional odds value (e.g., "1/2")
     * @param string $moneyline The moneyline odds value (e.g., "+100" or "-150")
     */
    public function __construct(float $decimal, string $fractional, string $moneyline)
    {
        if ($decimal < 1.0) {
            throw new InvalidPriceException(sprintf('Invalid decimal value provided: %F. Min value: 1.0', $decimal));
        }

        $this->decimal = round($decimal, self::DECIMAL_PRECISION);
        $this->fractional = $fractional;
        $this->moneyline = $moneyline;
        $this->probability = $this->calculateProbability($decimal);
    }

    /**
     * Get the decimal odds value.
     */
    public function getDecimal(): float
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
    public function getProbability(): float
    {
        return $this->probability;
    }

    /**
     * Calculate probability from decimal odds.
     */
    private function calculateProbability(float $decimal): float
    {
        if ($decimal <= 0) {
            throw new InvalidPriceException('Decimal odds must be greater than 0');
        }

        return 1.0 / $decimal;
    }
}
