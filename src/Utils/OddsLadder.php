<?php

declare(strict_types=1);

namespace Praetorian\Formatter\Odds\Utils;

use Decimal\Decimal;
use Praetorian\Formatter\Odds\DecimalOdd;
use Praetorian\Formatter\Odds\FractionalOdd;

class OddsLadder
{
    public static function decimalToFractional(DecimalOdd $decimalOdd): FractionalOdd
    {
        $fractionalString = self::decimalToFractionalString($decimalOdd);

        [$numerator, $denominator] = explode('/', $fractionalString);

        return new FractionalOdd((int) $numerator, (int) $denominator);
    }

    private static function decimalToFractionalString(DecimalOdd $decimalOdd): string
    {
        $decimalValue = (string) $decimalOdd->value();

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
            //'1.36' => '7/20',
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
}
