<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds;

use Praetorian\Formatter\Odds\Exception\InvalidPriceException;
use Praetorian\Formatter\Odds\Utils\OddsLadder;

/**
 * Class MoneylineOdd.
 */
final class MoneylineOdd extends Odd
{
    private const PLUS_SIGN = '+';

    /**
     * @param float $value
     */
    public function __construct(private float $value)
    {
    }

    public function value(): string
    {
        $sign = '';

        if ($this->value > 0) {
            $sign = self::PLUS_SIGN;
        }

        return $sign.$this->value;
    }

    /**
     * @throws InvalidPriceException
     */
    public function toDecimal(): DecimalOdd
    {
        $value = 1;

        if ($this->value > 0) {
            $value = $this->value / 100 + 1;
        } elseif ($this->value < 0) {
            $value = -100 / $this->value + 1;
        }

        return new DecimalOdd(round($value, self::DECIMAL_PRECISION));
    }

    /**
     * @throws \InvalidArgumentException
     */
    /**
     * @throws \InvalidArgumentException
     */
    public function toFractional(float $tolerance = 1.e-6, bool $useOddsLadder = true): FractionalOdd
    {
        if ($useOddsLadder) {
            return OddsLadder::decimalToFractional($this->toDecimal());
        }

        if (0.0 === $this->value) {
            return new FractionalOdd(0, 1);
        }

        if ($this->value > 0) {
            $v = $this->value / 100;
        } elseif ($this->value < 0) {
            $v = -100 / $this->value;
        }

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
        return $this;
    }
}
