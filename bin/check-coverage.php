#!/usr/bin/env php
<?php
/**
 * Coverage checker script
 *
 * This script runs PHPUnit with coverage and checks if it's 100%
 */

// Set XDebug mode for coverage
putenv('XDEBUG_MODE=coverage');

// Run PHPUnit with coverage
$output = shell_exec('vendor/bin/phpunit --coverage-text --colors=never 2>&1');

if ($output === null) {
    echo "❌ Failed to run PHPUnit\n";
    exit(1);
}

echo $output;

// Extract coverage percentage
preg_match('/Lines:\s+([\d.]+)%/', $output, $matches);

if (empty($matches[1])) {
    echo "❌ Could not extract coverage percentage\n";
    echo "Raw output:\n";
    echo $output;
    exit(1);
}

$coverage = (float) $matches[1];

echo "\nDetected Coverage: {$coverage}%\n";

if ($coverage === 100.0) {
    echo "✅ Coverage is 100%\n";
    exit(0);
} else {
    echo "❌ Coverage is {$coverage}%, expected 100%\n";
    exit(1);
}