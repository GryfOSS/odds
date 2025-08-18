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

        $decimal = bcadd($decimal, '0', self::DECIMAL_PRECISION); // Normalize precision
        $fractional = $this->decimalToFractional($decimal);
        $moneyline = $this->decimalToMoneyline($decimal);

        return new Odds($decimal, $fractional, $moneyline);
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
        $decimal = bcadd(bcdiv((string)$numerator, (string)$denominator, 10), '1', self::DECIMAL_PRECISION);
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
    private function defaultDecimalToFractional(string $decimal, string $tolerance = '0.000001'): string
    {
        if (bccomp($decimal, '1.0', self::DECIMAL_PRECISION) === 0) {
            return '0/1';
        }

        // v = decimal - 1
        $v = bcsub($decimal, '1', 10);
        
        // For very precise calculations with continued fractions
        // Converting to float temporarily for the algorithm, but we'll validate the result
        $vFloat = floatval($v);
        $n = 1;
        $n2 = 0;
        $d = 0;
        $d2 = 1;
        $b = 1 / $vFloat;
        $toleranceFloat = floatval($tolerance);

        do {
            $b = 1 / $b;
            $a = \floor($b);
            $aux = $n;
            $n = $a * $n + $n2;
            $n2 = $aux;
            $aux = $d;
            $d = $a * $d + $d2;
            $d2 = $aux;
            $b -= $a;
        } while (\abs($vFloat - $n / $d) > $vFloat * $toleranceFloat);

        return intval($n) . '/' . intval($d);
    }

    /**
     * Convert decimal to moneyline.
     */
    private function decimalToMoneyline(string $decimal): string
    {
        if (bccomp($decimal, '1.0', self::DECIMAL_PRECISION) === 0) {
            return $this->formatMoneyline('0');
        }
        
        if (bccomp($decimal, '2.0', self::DECIMAL_PRECISION) >= 0) {
            // value = 100 * (decimal - 1)
            $value = bcmul('100', bcsub($decimal, '1', 10), self::DECIMAL_PRECISION);
        } else {
            // value = -100 / (decimal - 1)
            $value = bcdiv('-100', bcsub($decimal, '1', 10), self::DECIMAL_PRECISION);
        }

        return $this->formatMoneyline($value);
    }

    /**
     * Convert moneyline to decimal.
     */
    private function moneylineToDecimal(string $moneyline): string
    {
        $value = '1';

        if (bccomp($moneyline, '0', self::DECIMAL_PRECISION) > 0) {
            // value = moneyline / 100 + 1
            $value = bcadd(bcdiv($moneyline, '100', 10), '1', self::DECIMAL_PRECISION);
        } elseif (bccomp($moneyline, '0', self::DECIMAL_PRECISION) < 0) {
            // value = -100 / moneyline + 1
            $value = bcadd(bcdiv('-100', $moneyline, 10), '1', self::DECIMAL_PRECISION);
        }

        return $value;
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

        // Remove unnecessary decimal places if the value is a whole number
        $intValue = bcadd($value, '0', 0); // Round to integer
        if (bccomp($value, $intValue, self::DECIMAL_PRECISION) === 0) {
            return $sign . $intValue;
        }

        return $sign . $value;
    }
}
