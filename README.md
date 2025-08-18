# Odds Formatter

[![Tests](https://github.com/gryfoss/odds/workflows/Tests/badge.svg?branch=new_api)](https://github.com/gryfoss/odds/actions)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)](https://github.com/gryfoss/odds/actions)
[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)

PHP package for dealing with different formats of betting odds: decimal (European), fractional (British), and moneyline (American).

## 🚀 New Architecture (v2.0)

This library has been completely redesigned with:

- **Immutable `Odds` class** containing all formats and probability
- **`OddsFactory`** with dependency injection for conversion strategies
- **String-based decimals** for precision and security (no more float issues!)
- **bcmath calculations** for exact mathematical operations
- **Extensible odds ladder system** via interfaces

## Features

- ✅ **Precision**: String-based decimals with bcmath calculations
- ✅ **Immutable design**: Thread-safe odds objects
- ✅ **All-in-one**: Single object contains decimal, fractional, moneyline, and probability
- ✅ **Dependency injection**: Configurable conversion strategies
- ✅ **Extensible**: Custom odds ladder implementations
- ✅ **Comprehensive**: Full test coverage

## Requirements

- PHP 8.0+
- bcmath extension (standard in most installations)
- Composer

## Installation

```bash
composer require gryfoss/odds
```

## Quick Start

```php
require 'vendor/autoload.php';

use GryfOSS\Odds\OddsFactory;

$factory = new OddsFactory();

// Create from string decimal (secure, precise)
$odds = $factory->fromDecimal('2.50');

echo $odds->getDecimal();     // "2.50"
echo $odds->getFractional();  // "3/2"
echo $odds->getMoneyline();   // "+150"
echo $odds->getProbability(); // "40.00"
```

## Usage Examples

### Basic Conversions

```php
$factory = new OddsFactory();

// From decimal
$odds = $factory->fromDecimal('1.75');

// From fractional
$odds = $factory->fromFractional(3, 4);

// From moneyline
$odds = $factory->fromMoneyline('-133');
```

### With Odds Ladder

```php
use GryfOSS\Odds\Utils\OddsLadder;

$oddsLadder = new OddsLadder();
$factory = new OddsFactory($oddsLadder);

$odds = $factory->fromDecimal('2.00');
echo $odds->getFractional(); // Uses odds ladder lookup
```

### Custom Odds Ladder

```php
use GryfOSS\Odds\Utils\OddsLadder;

class MyCustomLadder extends OddsLadder
{
    protected function getLadder(): array
    {
        return [
            '1.50' => 'evens',
            '2.00' => '1/1',
            '3.00' => '2/1',
        ];
    }
}

$factory = new OddsFactory(new MyCustomLadder());
$odds = $factory->fromDecimal('1.90');
echo $odds->getFractional(); // "evens"
```

## Migration from v1.x

See [STRING_DECIMAL_GUIDE.md](STRING_DECIMAL_GUIDE.md) for detailed migration instructions.

**Key Changes:**
- Use `OddsFactory` instead of individual odd classes
- Pass decimals as strings: `'2.50'` instead of `2.50`
- All return values are strings for precision
- Single `Odds` object contains all formats

## Documentation

- [String Decimal Guide](STRING_DECIMAL_GUIDE.md) - Precision and migration
- [NEW_API.md](NEW_API.md) - Complete API documentation

## License

This is an open-sourced software licensed under the [MIT license](LICENSE).
