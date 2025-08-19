<?php

declare(strict_types=1);

namespace GryfOSS\Odds;

use GryfOSS\Odds\Exception\InvalidPriceException;

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
        if (!is_numeric($decimal) || bccomp($decimal, '1.0', self::DECIMAL_PRECISION) < 0) {
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
        // Using bcmath for precise calculation
        $decimal = bcadd(bcdiv((string)$numerator, (string)$denominator, 4), '1', 4);
        $decimal = $this->bcRound($decimal, self::DECIMAL_PRECISION);
        $decimalInt = $this->stringToInt($decimal);
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
        if (bccomp($decimal, '1.00', self::DECIMAL_PRECISION) === 0) {
            return $this->formatMoneyline('0');
        }

        if (bccomp($decimal, '2.00', self::DECIMAL_PRECISION) >= 0) {
            // value = 100 * (decimal - 1)
            $decimalMinus1 = bcsub($decimal, '1', 4);
            $value = bcmul('100', $decimalMinus1, 4);
        } else {
            // value = -100 / (decimal - 1)
            $decimalMinus1 = bcsub($decimal, '1', 6); // Higher precision for division
            $value = bcdiv('-100', $decimalMinus1, 4);
        }

        // Round to 2 decimal places
        $roundedValue = $this->bcRound($value, self::DECIMAL_PRECISION);
        return $this->formatMoneyline($roundedValue);
    }

    /**
     * Convert moneyline to decimal.
     */
    private function moneylineToDecimal(string $moneyline): string
    {
        if (bccomp($moneyline, '0', 0) > 0) {
            // value = moneyline / 100 + 1
            $decimal = bcadd(bcdiv($moneyline, '100', 4), '1', 4); // Use higher precision
        } elseif (bccomp($moneyline, '0', 0) < 0) {
            // value = -100 / moneyline + 1
            $decimal = bcadd(bcdiv('-100', $moneyline, 4), '1', 4); // Use higher precision
        } else {
            $decimal = '1.00';
        }

        // Round to 2 decimal places using bcmath
        return $this->bcRound($decimal, self::DECIMAL_PRECISION);
    }

    /**
     * Format moneyline value with appropriate sign.
     */
    private function formatMoneyline(string $value): string
    {
        $sign = '';

        if (bccomp($value, '0', self::DECIMAL_PRECISION) > 0) {
            $sign = '+';
        }

        // Check if it's a whole number by comparing with truncated version
        $truncated = bcdiv($value, '1', 0);
        if (bccomp($value, $truncated, self::DECIMAL_PRECISION) === 0) {
            return $sign . $truncated;
        }

        // Format with proper decimal places using bcmath
        $rounded = $this->bcRound($value, self::DECIMAL_PRECISION);

        // Ensure it has exactly 2 decimal places if it's not a whole number
        if (strpos($rounded, '.') === false) {
            $rounded .= '.00';
        } elseif (strlen(substr($rounded, strpos($rounded, '.') + 1)) === 1) {
            $rounded .= '0';
        }

        return $sign . $rounded;
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
     * @todo in the future replace with native bcround
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
