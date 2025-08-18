<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

/**
 * Interface for odds ladder implementations.
 */
interface OddsLadderInterface
{
    /**
     * Convert decimal odds to fractional using odds ladder.
     */
    public function decimalToFractional(string $decimal): string;
}
