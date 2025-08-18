<?php

require_once 'vendor/autoload.php';

use Praetorian\Formatter\Odds\OddsFactory;
use Praetorian\Formatter\Odds\Utils\OddsLadder as UtilsOddsLadder;
use Praetorian\Formatter\Odds\OddsLadder;

echo "=== Enhanced Performance Benchmark ===\n\n";

// Test different configurations
$iterations = 50000;

// 1. Default mathematical conversion (no ladder)
echo "1. Default Mathematical Conversion (No Ladder)\n";
$factory = new OddsFactory();
$start = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $decimal = '1.' . str_pad((string)($i % 99 + 1), 2, '0', STR_PAD_LEFT);
    $odds = $factory->fromDecimal($decimal);
    $odds->getFractional(); // Trigger conversion
}

$defaultTime = microtime(true) - $start;
echo "- Time: " . number_format($defaultTime * 1000, 2) . " ms\n";
echo "- Per operation: " . number_format(($defaultTime / $iterations) * 1000000, 2) . " μs\n\n";

// 2. Integer-key odds ladder (optimized)
echo "2. Integer-Key Odds Ladder (New Optimized)\n";
$factory = new OddsFactory(new OddsLadder());
$start = microtime(true);

for ($i = 0; $i < $iterations; $i++) {
    $decimal = '1.' . str_pad((string)($i % 99 + 1), 2, '0', STR_PAD_LEFT);
    $odds = $factory->fromDecimal($decimal);
    $odds->getFractional(); // Trigger ladder lookup
}

$integerTime = microtime(true) - $start;
echo "- Time: " . number_format($integerTime * 1000, 2) . " ms\n";
echo "- Per operation: " . number_format(($integerTime / $iterations) * 1000000, 2) . " μs\n\n";

// 3. Utils odds ladder (if available) for comparison
if (class_exists(UtilsOddsLadder::class)) {
    echo "3. Utils Odds Ladder (Old Implementation)\n";
    $factory = new OddsFactory(new UtilsOddsLadder());
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $decimal = '1.' . str_pad((string)($i % 99 + 1), 2, '0', STR_PAD_LEFT);
        $odds = $factory->fromDecimal($decimal);
        $odds->getFractional(); // Trigger ladder lookup
    }

    $utilsTime = microtime(true) - $start;
    echo "- Time: " . number_format($utilsTime * 1000, 2) . " ms\n";
    echo "- Per operation: " . number_format(($utilsTime / $iterations) * 1000000, 2) . " μs\n\n";

    echo "Performance Comparison:\n";
    echo "- Integer ladder vs Utils: " . number_format(($utilsTime / $integerTime), 2) . "x faster\n";
}

echo "Key Optimizations:\n";
echo "✅ Integer keys: No string conversions for lookup\n";
echo "✅ Single conversion: String -> Int once per operation\n";
echo "✅ Fast comparisons: Integer <= operations\n";
echo "✅ No bcmath: Standard integer arithmetic\n\n";

// Demonstrate integer key efficiency
echo "Integer Key Lookup Demo:\n";
$ladder = [
    150 => '1/2',
    200 => '1/1',
    250 => '3/2',
    300 => '2/1',
];

$testValue = 175; // Represents 1.75
foreach ($ladder as $thresholdInt => $fractional) {
    echo "- {$testValue} <= {$thresholdInt}? " . ($testValue <= $thresholdInt ? 'YES -> ' . $fractional : 'NO') . "\n";
    if ($testValue <= $thresholdInt) {
        echo "  Found: {$fractional} (ultra-fast integer comparison)\n";
        break;
    }
}

echo "\nThroughput: ~" . number_format($iterations / $integerTime, 0) . " operations/second\n";
