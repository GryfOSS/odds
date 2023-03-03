<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

use Praetorian\Formatter\Odds\Utils\OddsLadder;

/**
 * Class DecimalOdd.
 */
final class DecimalOdd extends Odd
{
    private const MIN_VALUE = 1.0;

    /**
     * @param float $value
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(private float $value)
    {
        if ($value < self::MIN_VALUE) {
            throw new \InvalidArgumentException('Invalid value provided');
        }

        $value = round($value, self::DECIMAL_PRECISION);
    }

    public function value(): float
    {
        return $this->value;
    }

    public function toDecimal(): DecimalOdd
    {
        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function toFractional(float $tolerance = 1.e-6, bool $useOddsLadder = true): FractionalOdd
    {
        if ($useOddsLadder) {
            return OddsLadder::decimalToFractional($this);
        }

        if (1.0 === $this->value) {
            return new FractionalOdd(0, 1);
        }

        $v = $this->value - 1;
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

        return new FractionalOdd(intval($n), intval($d));
    }

    public function toMoneyline(): MoneylineOdd
    {
        if (1.0 === $this->value) {
            $value = 0;
        } elseif ($this->value >= 2) {
            $value = 100 * ($this->value - 1);
        } else {
            $value = -100 / ($this->value - 1);
        }

        return new MoneylineOdd(round($value, self::DECIMAL_PRECISION));
    }
}
