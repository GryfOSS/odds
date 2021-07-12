<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

/**
 * Interface OddInterface.
 */
interface OddInterface
{
    /**
     * @return mixed
     */
    public function value();

    public function toDecimal(): DecimalOdd;

    public function toFractional(): FractionalOdd;

    public function toMoneyline(): MoneylineOdd;
}
