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
        private bool $useOddsLadder = false
    ) {
        // useOddsLadder controls whether to use the built-in odds ladder or default conversion
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
        if ($this->useOddsLadder) {
            // Use the built-in odds ladder
            return $this->oddsLadderToFractional($decimal);
        }

        // Use default conversion (same as DecimalOdd::toFractional with useOddsLadder=false)
        return $this->defaultDecimalToFractional($decimal);
    }

    /**
     * Convert decimal to fractional using odds ladder.
     */
    private function oddsLadderToFractional(float $decimal): string
    {
        $decimalValue = (string) $decimal;

        $ladder = [
            '1.02' => '1/100',
            '1.03' => '1/50',
            '1.04' => '1/33',
            '1.06' => '1/20',
            '1.11' => '1/10',
            '1.12' => '1/9',
            '1.13' => '1/8',
            '1.14' => '1/7',
            '1.17' => '1/6',
            '1.21' => '1/5',
            '1.25' => '2/9',
            '1.28' => '1/4',
            '1.30' => '2/7',
            '1.33' => '3/10',
            '1.35' => '1/3',
            '1.40' => '4/11',
            '1.43' => '2/5',
            '1.45' => '4/9',
            '1.47' => '9/20',
            '1.50' => '40/85',
            '1.53' => '1/2',
            '1.57' => '8/15',
            '1.60' => '4/7',
            '1.62' => '3/5',
            '1.64' => '8/13',
            '1.66' => '5/8',
            '1.70' => '4/6',
            '1.72' => '7/10',
            '1.80' => '8/11',
            '1.91' => '4/5',
            '1.95' => '10/11',
            '2.00' => '20/21',
            '2.05' => '1/1',
            '2.10' => '21/20',
            '2.20' => '11/10',
            '2.25' => '6/5',
            '2.30' => '5/4',
            '2.38' => '13/10',
            '2.40' => '11/8',
            '2.50' => '7/5',
            '2.60' => '6/4',
            '2.63' => '8/5',
            '2.70' => '13/8',
            '2.75' => '17/10',
            '2.80' => '7/4',
            '2.88' => '9/5',
            '2.90' => '15/8',
            '3.00' => '19/10',
            '3.10' => '2/1',
            '3.13' => '21/10',
            '3.20' => '85/40',
            '3.38' => '11/5',
            '3.40' => '95/40',
            '3.50' => '12/5',
            '3.60' => '5/2',
            '3.75' => '13/5',
            '3.80' => '11/4',
            '4.00' => '14/5',
            '4.20' => '3/1',
            '4.33' => '16/5',
            '4.50' => '100/30',
            '4.60' => '7/2',
            '5.00' => '18/5',
            '5.50' => '4/1',
            '6.00' => '9/2',
            '6.50' => '5/1',
            '7.00' => '11/2',
            '7.50' => '6/1',
            '8.00' => '13/2',
            '8.50' => '7/1',
            '9.00' => '15/2',
            '9.50' => '8/1',
            '10.0' => '17/2',
        ];

        foreach ($ladder as $threshold => $value) {
            if (bccomp($decimalValue, $threshold) < 0) {
                return $value;
            }
        }

        return sprintf('%d/1', intval(bcsub($decimalValue, '0.5', 0)));
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
