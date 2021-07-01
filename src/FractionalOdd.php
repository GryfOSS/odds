<?php

namespace Alexsabdev\Odds;

/**
 * Class FractionalOdd
 * @package Alexsabdev\Odds
 */
final class FractionalOdd extends Odd
{
    private const MIN_NUMERATOR = 0;

    private const MIN_DENOMINATOR = 1;

    private const ALLOWED_FRACTION_BARS = ['/', '-'];

    /**
     * @param int $numerator
     * @param int $denominator
     * @param string $fractionBar
     * @throws \InvalidArgumentException
     */
    public function __construct(private int $numerator, private int $denominator, private string $fractionBar = '/')
    {
        if ($numerator < self::MIN_NUMERATOR) {
            throw new \InvalidArgumentException('Invalid numerator provided');
        }

        if ($denominator < self::MIN_DENOMINATOR) {
            throw new \InvalidArgumentException('Invalid denominator provided');
        }

        if (!\in_array($fractionBar, self::ALLOWED_FRACTION_BARS, true)) {
            throw new \InvalidArgumentException('Invalid fraction bar provided');
        }
    }

    /**
     * @return string
     */
    public function value() : string
    {
        return $this->numerator . $this->fractionBar . $this->denominator;
    }

    /**
     * @return DecimalOdd
     * @throws \InvalidArgumentException
     */
    public function toDecimal(): DecimalOdd
    {
        $value = $this->numerator / $this->denominator + 1.0;

        return new DecimalOdd(round($value, self::DECIMAL_PRECISION));
    }

    /**
     * @return FractionalOdd
     */
    public function toFractional(): FractionalOdd
    {
        return $this;
    }

    /**
     * @return MoneylineOdd
     */
    public function toMoneyline(): MoneylineOdd
    {
        $value = $this->numerator / $this->denominator;

        if ($value >= 2) {
            $value = 100 * $value;
        } elseif ($value > 0) {
            $value = -100 / $value;
        }

        return new MoneylineOdd(round($value, self::DECIMAL_PRECISION));
    }
}
