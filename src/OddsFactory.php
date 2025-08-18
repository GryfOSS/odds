<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

use Praetorian\Formatter\Odds\Exception\InvalidPriceException;

/**
 * Factory for creating Odds objects with configurable odds ladder.
 */
final class OddsFactory
{
    private const DECIMAL_PRECISION = 2;
    private const SCALE_FACTOR = 100; // For integer calculations

    public function __construct(
        private ?OddsLadderInterface $oddsLadder = null
    ) {
        // If no odds ladder is provided, will use default mathematical conversion
    }

    /**
     * Create Odds from decimal value.
     *
     * @throws InvalidPriceException
     */
    public function fromDecimal(string $decimal): Odds
    {
        if (!is_numeric($decimal) || (float)$decimal < 1.0) {
            throw new InvalidPriceException(sprintf('Invalid decimal value provided: %s. Min value: 1.0', $decimal));
        }

        $normalizedDecimal = $this->normalizeDecimal($decimal);
        $fractional = $this->decimalToFractional($normalizedDecimal);
        $moneyline = $this->decimalToMoneyline($normalizedDecimal);

        return new Odds($normalizedDecimal, $fractional, $moneyline);
    }

    /**
     * Create Odds from fractional value.
     *
     * @param int $numerator
     * @param int $denominator
     * @throws InvalidPriceException
     */
    public function fromFractional(int $numerator, int $denominator): Odds
    {
        if ($numerator < 0) {
            throw new InvalidPriceException('Invalid numerator provided');
        }

        if ($denominator < 1) {
            throw new InvalidPriceException('Invalid denominator provided');
        }

        // decimal = (numerator / denominator) + 1
        // Using integer math: ((numerator * SCALE_FACTOR) / denominator) + SCALE_FACTOR
        $decimalInt = (int)round(($numerator * self::SCALE_FACTOR) / $denominator) + self::SCALE_FACTOR;
        $decimal = $this->intToString($decimalInt);
        $fractional = $numerator . '/' . $denominator;
        $moneyline = $this->decimalToMoneyline($decimal);

        return new Odds($decimal, $fractional, $moneyline);
    }

    /**
     * Create Odds from moneyline value.
     *
     * @throws InvalidPriceException
     */
    public function fromMoneyline(string $moneyline): Odds
    {
        if (!is_numeric($moneyline)) {
            throw new InvalidPriceException(sprintf('Invalid moneyline value provided: %s', $moneyline));
        }

        $decimal = $this->moneylineToDecimal($moneyline);
        $fractional = $this->decimalToFractional($decimal);
        $moneylineFormatted = $this->formatMoneyline($moneyline);

        return new Odds($decimal, $fractional, $moneylineFormatted);
    }

    /**
     * Convert decimal to fractional using odds ladder or default conversion.
     */
    private function decimalToFractional(string $decimal): string
    {
        if ($this->oddsLadder !== null) {
            // Use the injected odds ladder
            return $this->oddsLadder->decimalToFractional($decimal);
        }

        // Use default conversion (same as DecimalOdd::toFractional with useOddsLadder=false)
        return $this->defaultDecimalToFractional($decimal);
    }

    /**
     * Default decimal to fractional conversion (without odds ladder).
     */
    private function defaultDecimalToFractional(string $decimal, int $tolerance = 1): string
    {
        $decimalInt = $this->stringToInt($decimal);

        if ($decimalInt === self::SCALE_FACTOR) { // 1.00
            return '0/1';
        }

        // v = decimal - 1 (in integer form)
        $vInt = $decimalInt - self::SCALE_FACTOR;

        // Convert to float for continued fractions algorithm
        $v = $vInt / self::SCALE_FACTOR;
        $toleranceFloat = $tolerance / (self::SCALE_FACTOR * self::SCALE_FACTOR);

        $n = 1;
        $n2 = 0;
        $d = 0;
        $d2 = 1;
        $b = 1 / $v;

        do {
            $b = 1 / $b;
            $a = floor($b);
            $aux = $n;
            $n = $a * $n + $n2;
            $n2 = $aux;
            $aux = $d;
            $d = $a * $d + $d2;
            $d2 = $aux;
            $b -= $a;
        } while (abs($v - $n / $d) > $v * $toleranceFloat);

        return intval($n) . '/' . intval($d);
    }

    /**
     * Convert decimal to moneyline.
     */
    private function decimalToMoneyline(string $decimal): string
    {
        $decimalInt = $this->stringToInt($decimal);

        if ($decimalInt === self::SCALE_FACTOR) { // 1.00
            return $this->formatMoneyline('0');
        }

        if ($decimalInt >= 2 * self::SCALE_FACTOR) { // >= 2.00
            // value = 100 * (decimal - 1)
            $valueInt = ($decimalInt - self::SCALE_FACTOR) * 100 / self::SCALE_FACTOR;
            $value = number_format($valueInt, self::DECIMAL_PRECISION, '.', '');
        } else {
            // value = -100 / (decimal - 1)
            $divisor = ($decimalInt - self::SCALE_FACTOR) / self::SCALE_FACTOR;
            $valueFloat = -100 / $divisor;
            $value = number_format($valueFloat, self::DECIMAL_PRECISION, '.', '');
        }

        return $this->formatMoneyline($value);
    }

    /**
     * Convert moneyline to decimal.
     */
    private function moneylineToDecimal(string $moneyline): string
    {
        $moneylineFloat = (float)$moneyline;

        if ($moneylineFloat > 0) {
            // value = moneyline / 100 + 1
            $valueInt = (int)round(($moneylineFloat / 100 + 1) * self::SCALE_FACTOR);
        } elseif ($moneylineFloat < 0) {
            // value = -100 / moneyline + 1
            $valueInt = (int)round((-100 / $moneylineFloat + 1) * self::SCALE_FACTOR);
        } else {
            $valueInt = self::SCALE_FACTOR; // 1.00
        }

        return $this->intToString($valueInt);
    }

    /**
     * Format moneyline value with appropriate sign.
     */
    private function formatMoneyline(string $value): string
    {
        $valueFloat = (float)$value;
        $sign = '';

        if ($valueFloat > 0) {
            $sign = '+';
        }

        // Remove unnecessary decimal places if the value is a whole number
        if ($valueFloat == intval($valueFloat)) {
            return $sign . intval($valueFloat);
        }

        return $sign . number_format($valueFloat, self::DECIMAL_PRECISION, '.', '');
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
