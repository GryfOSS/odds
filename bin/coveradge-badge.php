#!/usr/bin/env php
<?php
/**
 * Coverage badge script
 *
 * Outputs coverage percentage in a format suitable for badges
 */

// Set XDebug mode for coverage
putenv('XDEBUG_MODE=coverage');

// Run PHPUnit with coverage (quiet mode)
$output = shell_exec('vendor/bin/phpunit --coverage-text --colors=never 2>/dev/null');

if ($output === null) {
    echo "0";
    exit(1);
}

// Extract coverage percentage
preg_match('/Lines:\s+([\d.]+)%/', $output, $matches);

if (empty($matches[1])) {
    echo "0";
    exit(1);
}

$coverage = (float) $matches[1];
echo number_format($coverage, 1);

exit(0);