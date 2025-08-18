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
    public function fromDecimal(float $decimal): Odds
    {
        if ($decimal < 1.0) {
            throw new InvalidPriceException(sprintf('Invalid decimal value provided: %F. Min value: 1.0', $decimal));
        }

        $decimal = round($decimal, self::DECIMAL_PRECISION);
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

        $decimal = $numerator / $denominator + 1.0;
        $decimal = round($decimal, self::DECIMAL_PRECISION);
        $fractional = $numerator . '/' . $denominator;
        $moneyline = $this->decimalToMoneyline($decimal);

        return new Odds($decimal, $fractional, $moneyline);
    }

    /**
     * Create Odds from moneyline value.
     *
     * @throws InvalidPriceException
     */
    public function fromMoneyline(float $moneyline): Odds
    {
        $decimal = $this->moneylineToDecimal($moneyline);
        $decimal = round($decimal, self::DECIMAL_PRECISION);
        $fractional = $this->decimalToFractional($decimal);
        $moneylineFormatted = $this->formatMoneyline($moneyline);

        return new Odds($decimal, $fractional, $moneylineFormatted);
    }

    /**
     * Convert decimal to fractional using odds ladder or default conversion.
     */
    private function decimalToFractional(float $decimal): string
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
    private function defaultDecimalToFractional(float $decimal, float $tolerance = 1.e-6): string
    {
        if (abs($decimal - 1.0) < 0.001) {
            return '0/1';
        }

        $v = $decimal - 1;
        $n = 1;
        $n2 = 0;
        $d = 0;
        $d2 = 1;
        $b = 1 / $v;

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
        } while (\abs($v - $n / $d) > $v * $tolerance);

        return intval($n) . '/' . intval($d);
    }

    /**
     * Convert decimal to moneyline.
     */
    private function decimalToMoneyline(float $decimal): string
    {
        if (abs($decimal - 1.0) < 0.001) {
            $value = 0;
        } elseif ($decimal >= 2) {
            $value = 100 * ($decimal - 1);
        } else {
            $value = -100 / ($decimal - 1);
        }

        $rounded = round($value, self::DECIMAL_PRECISION);
        return $this->formatMoneyline($rounded);
    }

    /**
     * Convert moneyline to decimal.
     */
    private function moneylineToDecimal(float $moneyline): float
    {
        $value = 1;

        if ($moneyline > 0) {
            $value = $moneyline / 100 + 1;
        } elseif ($moneyline < 0) {
            $value = -100 / $moneyline + 1;
        }

        return $value;
    }

    /**
     * Format moneyline value with appropriate sign.
     */
    private function formatMoneyline(float $value): string
    {
        $sign = '';

        if ($value > 0) {
            $sign = '+';
        }

        // Remove unnecessary decimal places if the value is a whole number
        if ($value == intval($value)) {
            return $sign . intval($value);
        }

        return $sign . $value;
    }
}
