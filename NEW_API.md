# New Odds API Documentation

## Overview

The library has been reorganized to provide an immutable `Odds` class that contains all supported odds formats (Decimal, Fractional, Moneyline) and calculated probability. The `OddsFactory` class is used to create `Odds` objects with configurable odds ladder support via dependency injection.

## Key Features

- **Immutable Odds Class**: Once created, odds values cannot be changed
- **All Formats Included**: Each `Odds` object contains decimal, fractional, moneyline, and probability
- **Dependency Injection**: Inject custom odds ladder implementations
- **Extensible Design**: Base odds ladder class can be extended with custom lookup tables
- **Type Safety**: Full type hints and strict validation
- **Simplified API**: Only two classes needed - `Odds` and `OddsFactory`

## Usage Examples

### Basic Usage

```php
use Praetorian\Formatter\Odds\OddsFactory;

// Create factory with default mathematical conversion
$factory = new OddsFactory();

// Create odds from decimal
$odds = $factory->fromDecimal(2.5);
echo $odds->getDecimal();    // 2.5
echo $odds->getFractional(); // 3/2 (mathematical conversion)
echo $odds->getMoneyline();  // +150
echo $odds->getProbability(); // 0.4

// Create odds from fractional
$odds = $factory->fromFractional(3, 2); // 3/2
echo $odds->getDecimal();    // 2.5
echo $odds->getFractional(); // 3/2
echo $odds->getMoneyline();  // +150
echo $odds->getProbability(); // 0.4

// Create odds from moneyline
$odds = $factory->fromMoneyline(-150);
echo $odds->getDecimal();    // 1.67
echo $odds->getFractional(); // 67/100 (mathematical conversion)
echo $odds->getMoneyline();  // -150
echo $odds->getProbability(); // 0.6
```

### Using Standard Odds Ladder

```php
use Praetorian\Formatter\Odds\OddsFactory;
use Praetorian\Formatter\Odds\OddsLadder;

// Create factory with standard odds ladder
$factory = new OddsFactory(new OddsLadder());

$odds = $factory->fromDecimal(1.91);
echo $odds->getFractional(); // 20/21 (from odds ladder)
```

### Creating Custom Odds Ladder

```php
use Praetorian\Formatter\Odds\OddsLadder;

class MyCustomOddsLadder extends OddsLadder
{
    protected function getLadder(): array
    {
        return [
            '1.25' => '1/4',
            '1.50' => '1/2',
            '2.00' => 'evens',
            '3.00' => '2/1',
            '4.00' => '3/1',
        ];
    }

    // Optionally override fallback behavior
    protected function fallbackConversion(float $decimal): string
    {
        return round($decimal - 1) . '/1';
    }
}

// Use your custom ladder
$customFactory = new OddsFactory(new MyCustomOddsLadder());
$odds = $customFactory->fromDecimal(1.9);
echo $odds->getFractional(); // 'evens' (from custom ladder)
```

### Comparing Different Conversion Methods

```php
$defaultFactory = new OddsFactory();
$ladderFactory = new OddsFactory(new OddsLadder());
$customFactory = new OddsFactory(new MyCustomOddsLadder());

$decimal = 2.0;
echo $defaultFactory->fromDecimal($decimal)->getFractional(); // 1/1 (mathematical)
echo $ladderFactory->fromDecimal($decimal)->getFractional();  // 19/10 (standard ladder)
echo $customFactory->fromDecimal($decimal)->getFractional();  // 2/1 (custom ladder fallback)
```

## API Reference

### Odds Class

The `Odds` class is immutable and provides the following getters:

- `getDecimal(): float` - Returns the decimal odds value
- `getFractional(): string` - Returns the fractional odds as a string (e.g., "3/2")
- `getMoneyline(): string` - Returns the moneyline odds with appropriate sign (e.g., "+150", "-200")
- `getProbability(): float` - Returns the calculated probability (0.0 to 1.0)

### OddsFactory Class

The `OddsFactory` class provides factory methods to create `Odds` objects:

- `fromDecimal(float $decimal): Odds` - Create odds from decimal value
- `fromFractional(int $numerator, int $denominator): Odds` - Create odds from fractional values
- `fromMoneyline(float $moneyline): Odds` - Create odds from moneyline value

### Constructor

```php
new OddsFactory(?OddsLadderInterface $oddsLadder = null)
```

- `$oddsLadder` - Optional odds ladder implementation. If `null`, uses mathematical conversion.

### OddsLadder Classes

#### Base OddsLadder Class
- Implements `OddsLadderInterface`
- Provides standard betting industry odds ladder
- Can be extended for custom behavior

#### Custom Implementation
Extend the base class and override:
- `getLadder(): array` - Return your custom lookup table
- `fallbackConversion(float $decimal): string` - Handle values not in the ladder

## Migration from Old API

### Before (Old API)
```php
$decimalOdd = new DecimalOdd(2.5);
$fractional = $decimalOdd->toFractional();
$moneyline = $decimalOdd->toMoneyline();
```

### After (New API)
```php
$factory = new OddsFactory();
$odds = $factory->fromDecimal(2.5);
$decimal = $odds->getDecimal();
$fractional = $odds->getFractional();
$moneyline = $odds->getMoneyline();
$probability = $odds->getProbability(); // New!
```

## Conversion Methods

### Default Mathematical Conversion
When no odds ladder is injected (default):
- Uses precise mathematical conversion with continued fractions algorithm
- More accurate for uncommon odds values
- Results in mathematically correct fractions

### Standard Odds Ladder
When `new OddsLadder()` is injected:
- Uses predefined lookup table matching betting industry standards
- Provides "rounded" fractions that bookmakers typically use
- Better for user-facing applications where standard betting fractions are expected

### Custom Odds Ladder
When custom implementation is injected:
- Complete control over conversion behavior
- Define your own lookup table and fallback logic
- Perfect for specific business requirements or regional standards

## Benefits

1. **Dependency Injection**: Clean, testable, flexible architecture
2. **Extensibility**: Easy to create custom odds ladder implementations
3. **Immutability**: No risk of accidental modification
4. **Complete Information**: All formats calculated once at creation
5. **Probability Included**: No need for separate calculation
6. **Type Safe**: Full PHP 8+ type hints with interfaces
7. **Simplified**: Clean API with dependency injection pattern
