<?php

declare(strict_types=1);

namespace GryfOSS\Odds;

/**
 * Example of a custom odds ladder with modified lookup table.
 */
class CustomOddsLadder extends OddsLadder
{
    /**
     * Override the ladder with custom integer-keyed values.
     */
    protected function getLadder(): array
    {
        return [
            120 => '1/5',      // 1.20
            125 => '1/4',      // 1.25
            133 => '1/3',      // 1.33
            150 => '1/2',      // 1.50
            200 => '1/1',      // 2.00
            250 => '3/2',      // 2.50
            300 => '2/1',      // 3.00
            400 => '3/1',      // 4.00
            500 => '4/1',      // 5.00
            600 => '5/1',      // 6.00
        ];
    }
}
