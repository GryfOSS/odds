<?php

require_once 'vendor/autoload.php';

use GryfOSS\Odds\OddsFactory;
use GryfOSS\Odds\Utils\OddsLadder;
use GryfOSS\Odds\CustomOddsLadder;

echo "=== String-based Decimal Odds System ===\n\n";

// 1. Default conversion (mathematical)
$factory = new OddsFactory();

echo "1. Default mathematical conversion:\n";
$odds = $factory->fromDecimal('2.50');
echo "Decimal: {$odds->getDecimal()}, Fractional: {$odds->getFractional()}, Moneyline: {$odds->getMoneyline()}, Probability: {$odds->getProbability()}%\n\n";

// 2. Standard odds ladder conversion
$standardLadder = new OddsLadder();
$factoryWithLadder = new OddsFactory($standardLadder);

echo "2. Standard odds ladder conversion:\n";
$odds = $factoryWithLadder->fromDecimal('2.50');
echo "Decimal: {$odds->getDecimal()}, Fractional: {$odds->getFractional()}, Moneyline: {$odds->getMoneyline()}, Probability: {$odds->getProbability()}%\n\n";

// 3. Custom odds ladder conversion
$customLadder = new CustomOddsLadder();
$factoryWithCustom = new OddsFactory($customLadder);

echo "3. Custom odds ladder conversion:\n";
$odds = $factoryWithCustom->fromDecimal('1.90');
echo "Decimal: {$odds->getDecimal()}, Fractional: {$odds->getFractional()}, Moneyline: {$odds->getMoneyline()}, Probability: {$odds->getProbability()}%\n\n";

// 4. Test precision with high precision input
echo "4. High precision test:\n";
$odds = $factory->fromDecimal('2.33333333');
echo "Input: 2.33333333 -> Normalized: {$odds->getDecimal()}, Probability: {$odds->getProbability()}%\n\n";

// 5. Test from fractional and moneyline
echo "5. From fractional (3/2):\n";
$odds = $factory->fromFractional(3, 2);
echo "Decimal: {$odds->getDecimal()}, Fractional: {$odds->getFractional()}, Moneyline: {$odds->getMoneyline()}, Probability: {$odds->getProbability()}%\n\n";

echo "6. From moneyline (+150):\n";
$odds = $factory->fromMoneyline('150');
echo "Decimal: {$odds->getDecimal()}, Fractional: {$odds->getFractional()}, Moneyline: {$odds->getMoneyline()}, Probability: {$odds->getProbability()}%\n\n";

echo "All calculations performed using bcmath for precision!\n";
