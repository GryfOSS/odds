<?php

declare(strict_types=1);

namespace GryfOSS\Odds;

/**
 * Base odds ladder implementation with configurable lookup table.
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

        foreach ($ladder as $thresholdInt => $value) {
            if ($decimalInt <= $thresholdInt) {
                return $value;
            }
        }

        // Fallback for high odds
        return $this->fallbackConversion($decimal);
    }

    /**
     * Get the odds ladder lookup table with integer keys.
     * Override this method in subclasses to provide custom ladder.
     */
    protected function getLadder(): array
    {
        return [
            102 => '1/50',      // 1.02
            103 => '1/33',      // 1.03
            104 => '1/25',      // 1.04
            105 => '1/20',      // 1.05
            106 => '1/17',      // 1.06
            107 => '1/15',      // 1.07
            108 => '2/25',      // 1.08
            109 => '1/12',      // 1.09
            110 => '1/10',      // 1.10
            111 => '1/9',       // 1.11
            113 => '1/8',       // 1.13
            114 => '1/7',       // 1.14
            117 => '1/6',       // 1.17
            120 => '1/5',       // 1.20
            122 => '2/9',       // 1.22
            125 => '1/4',       // 1.25
            129 => '2/7',       // 1.29
            133 => '1/3',       // 1.33
            136 => '4/11',      // 1.36
            140 => '2/5',       // 1.40
            144 => '4/9',       // 1.44
            150 => '1/2',       // 1.50
            157 => '4/7',       // 1.57
            162 => '8/13',      // 1.62
            167 => '4/6',       // 1.67
            173 => '8/11',      // 1.73
            180 => '4/5',       // 1.80
            191 => '10/11',     // 1.91
            200 => '1/1',       // 2.00
            210 => '11/10',     // 2.10
            220 => '6/5',       // 2.20
            238 => '11/8',      // 2.38
            250 => '3/2',       // 2.50
            262 => '8/5',       // 2.62
            275 => '7/4',       // 2.75
            300 => '2/1',       // 3.00
            325 => '9/4',       // 3.25
            350 => '5/2',       // 3.50
            400 => '3/1',       // 4.00
            450 => '7/2',       // 4.50
            500 => '4/1',       // 5.00
            600 => '5/1',       // 6.00
            700 => '6/1',       // 7.00
            800 => '7/1',       // 8.00
            900 => '8/1',       // 9.00
            1000 => '9/1',      // 10.00
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
