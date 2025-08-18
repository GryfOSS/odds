<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

use Praetorian\Formatter\Odds\Utils\OddsLadder;

/**
 * Example of a custom odds ladder with modified lookup table.
 */
class CustomOddsLadder extends OddsLadder
{
    /**
     * Override the ladder with custom values.
     */
    protected function getLadder(): array
    {
        return [
            '1.20' => '1/5',
            '1.25' => '1/4',
            '1.33' => '1/3',
            '1.50' => '1/2',
            '2.00' => 'evens', // For values < 2.0
            '2.50' => '3/2',   // For values < 2.5
            '3.00' => '2/1',   // For values < 3.0
            '4.00' => '3/1',   // For values < 4.0
            '5.00' => '4/1',   // For values < 5.0
            '6.00' => '5/1',   // For values < 6.0
        ];
    }

    /**
     * Custom fallback for values not in the ladder.
     */
    protected function fallbackConversion(string $decimal): string
    {
        // Round to nearest whole number for simplicity
        $rounded = bcsub($decimal, '1', 0); // (decimal - 1) rounded to integer
        return $rounded . '/1';
    }
}
