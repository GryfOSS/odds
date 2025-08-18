<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use GryfOSS\Odds\OddsFactory;
use GryfOSS\Odds\OddsLadder;
use GryfOSS\Odds\CustomOddsLadder;

// Example usage of the new Odds library with dependency injection

// Create a factory with default conversion (no odds ladder)
$factory = new OddsFactory();

// Create odds from decimal
echo "=== Creating Odds from Decimal (Default Conversion) ===\n";
$odds1 = $factory->fromDecimal(2.5);
echo "Decimal: " . $odds1->getDecimal() . "\n";
echo "Fractional: " . $odds1->getFractional() . "\n";
echo "Moneyline: " . $odds1->getMoneyline() . "\n";
echo "Probability: " . $odds1->getProbability() . "\n\n";

// Create odds from fractional
echo "=== Creating Odds from Fractional ===\n";
$odds2 = $factory->fromFractional(3, 2);  // 3/2
echo "Decimal: " . $odds2->getDecimal() . "\n";
echo "Fractional: " . $odds2->getFractional() . "\n";
echo "Moneyline: " . $odds2->getMoneyline() . "\n";
echo "Probability: " . $odds2->getProbability() . "\n\n";

// Create odds from moneyline
echo "=== Creating Odds from Moneyline ===\n";
$odds3 = $factory->fromMoneyline(-150);
echo "Decimal: " . $odds3->getDecimal() . "\n";
echo "Fractional: " . $odds3->getFractional() . "\n";
echo "Moneyline: " . $odds3->getMoneyline() . "\n";
echo "Probability: " . $odds3->getProbability() . "\n\n";

// Create factory with standard odds ladder
echo "=== Using Standard Odds Ladder ===\n";
$ladderFactory = new OddsFactory(new OddsLadder());

$odds4 = $ladderFactory->fromDecimal(1.91);
echo "Decimal: " . $odds4->getDecimal() . "\n";
echo "Fractional: " . $odds4->getFractional() . "\n";
echo "Moneyline: " . $odds4->getMoneyline() . "\n";
echo "Probability: " . $odds4->getProbability() . "\n\n";

// Create factory with custom odds ladder
echo "=== Using Custom Odds Ladder ===\n";
$customLadderFactory = new OddsFactory(new CustomOddsLadder());

$odds5 = $customLadderFactory->fromDecimal(1.9);
echo "Decimal: " . $odds5->getDecimal() . "\n";
echo "Fractional: " . $odds5->getFractional() . "\n";
echo "Moneyline: " . $odds5->getMoneyline() . "\n";
echo "Probability: " . $odds5->getProbability() . "\n\n";

echo "=== Comparing Different Conversion Methods ===\n";
$testDecimal = 2.0;

$defaultOdds = $factory->fromDecimal($testDecimal);
$ladderOdds = $ladderFactory->fromDecimal($testDecimal);
$customOdds = $customLadderFactory->fromDecimal($testDecimal);

echo "Decimal " . $testDecimal . ":\n";
echo "Default conversion: " . $defaultOdds->getFractional() . "\n";
echo "Standard odds ladder: " . $ladderOdds->getFractional() . "\n";
echo "Custom odds ladder: " . $customOdds->getFractional() . "\n\n";

echo "=== Demonstrating Immutability ===\n";
echo "odds1 decimal: " . $odds1->getDecimal() . "\n";
echo "odds1 decimal (second call): " . $odds1->getDecimal() . "\n";
echo "Objects are immutable - values never change!\n";
