<?php

declare(strict_types=1);

namespace GryfOSS\Odds\Utils;

use GryfOSS\Odds\OddsLadderInterface;

/**
 * Standard odds ladder implementation.
 */
class OddsLadder implements OddsLadderInterface
{
    private const DECIMAL_PRECISION = 2;
    private const SCALE_FACTOR = 100; // For integer calculations

    /**
     * Convert decimal odds to fractional using odds ladder lookup.
     */
    public function decimalToFractional(string $decimal): string
    {
        $decimalInt = $this->stringToInt($decimal);
        $ladder = $this->getLadder();

        foreach ($ladder as $threshold => $value) {
            $thresholdInt = $this->stringToInt($threshold);
            if ($decimalInt <= $thresholdInt) {
                return $value;
            }
        }

        // Fallback for high odds
        return $this->fallbackConversion($decimal);
    }

    /**
     * Get the odds ladder lookup table.
     * Override this method in subclasses to provide custom ladders.
     */
    protected function getLadder(): array
    {
        return [
            '1.02' => '1/50',
            '1.03' => '1/33',
            '1.04' => '1/25',
            '1.05' => '1/20',
            '1.06' => '1/17',
            '1.07' => '1/15',
            '1.08' => '2/25',
            '1.09' => '1/12',
            '1.10' => '1/10',
            '1.11' => '1/9',
            '1.13' => '1/8',
            '1.14' => '1/7',
            '1.17' => '1/6',
            '1.20' => '1/5',
            '1.22' => '2/9',
            '1.25' => '1/4',
            '1.29' => '2/7',
            '1.33' => '1/3',
            '1.36' => '4/11',
            '1.40' => '2/5',
            '1.44' => '4/9',
            '1.50' => '1/2',
            '1.57' => '4/7',
            '1.62' => '8/13',
            '1.67' => '4/6',
            '1.73' => '8/11',
            '1.80' => '4/5',
            '1.91' => '10/11',
            '2.00' => '1/1',
            '2.10' => '11/10',
            '2.20' => '6/5',
            '2.38' => '11/8',
            '2.50' => '3/2',
            '2.62' => '8/5',
            '2.75' => '7/4',
            '3.00' => '2/1',
            '3.25' => '9/4',
            '3.50' => '5/2',
            '4.00' => '3/1',
            '4.50' => '7/2',
            '5.00' => '4/1',
            '6.00' => '5/1',
            '7.00' => '6/1',
            '8.00' => '7/1',
            '9.00' => '8/1',
            '10.00' => '9/1',
        ];
    }

    /**
     * Fallback conversion for odds not in the ladder.
     */
    protected function fallbackConversion(string $decimal): string
    {
        // For high odds, return (decimal - 1)/1
        $decimalInt = $this->stringToInt($decimal);
        $numerator = ($decimalInt - self::SCALE_FACTOR) / self::SCALE_FACTOR;
        return intval($numerator) . '/1';
    }

    /**
     * Convert string decimal to integer (multiply by 100).
     * E.g., "2.50" -> 250
     */
    protected function stringToInt(string $decimal): int
    {
        return (int)round((float)$decimal * self::SCALE_FACTOR);
    }

    /**
     * Convert integer back to string decimal (divide by 100).
     * E.g., 250 -> "2.50"
     */
    protected function intToString(int $value): string
    {
        return number_format($value / self::SCALE_FACTOR, self::DECIMAL_PRECISION, '.', '');
    }
}
