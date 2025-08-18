Feature: Comprehensive Conversion Testing
  As a developer using the odds formatter
  I want to test multiple conversion scenarios at once
  So that I can verify consistency across different input formats

  Background:
    Given I have an odds factory

  Scenario: Batch decimal to all formats conversion
    Given I have the following conversion scenarios:
      | decimal | fractional | moneyline | probability |
      | 1.01    | 1/100      | -10000    | 99.01       |
      | 1.50    | 1/2        | -200      | 66.67       |
      | 2.00    | 1/1        | +100      | 50.00       |
      | 2.50    | 3/2        | +150      | 40.00       |
      | 3.00    | 2/1        | +200      | 33.33       |
      | 4.00    | 3/1        | +300      | 25.00       |
      | 5.00    | 4/1        | +400      | 20.00       |
      | 10.00   | 9/1        | +900      | 10.00       |

  Scenario: Batch fractional to decimal conversion
    Given I test the following fractional to decimal conversions:
      | fractional | decimal | moneyline |
      | 0/1        | 1.00    | 0         |
      | 1/10       | 1.10    | -1000     |
      | 1/5        | 1.20    | -500      |
      | 1/4        | 1.25    | -400      |
      | 1/3        | 1.33    | -303.03   |
      | 2/5        | 1.40    | -250      |
      | 1/2        | 1.50    | -200      |
      | 3/5        | 1.60    | -166.67   |
      | 4/6        | 1.67    | -149.25   |
      | 4/5        | 1.80    | -125      |
      | 1/1        | 2.00    | +100      |
      | 6/5        | 2.20    | +120      |
      | 3/2        | 2.50    | +150      |
      | 2/1        | 3.00    | +200      |
      | 5/2        | 3.50    | +250      |
      | 3/1        | 4.00    | +300      |
      | 9/2        | 5.50    | +450      |
      | 5/1        | 6.00    | +500      |
      | 10/1       | 11.00   | +1000     |

  Scenario: Batch moneyline to decimal conversion
    Given I test the following moneyline to decimal conversions:
      | moneyline | decimal | fractional |
      | 0         | 1.00    | 0/1        |
      | -1000     | 1.10    | 1/10       |
      | -500      | 1.20    | 1/5        |
      | -400      | 1.25    | 1/4        |
      | -300      | 1.33    | 33/100     |
      | -200      | 1.50    | 1/2        |
      | -150      | 1.67    | 67/100     |
      | -125      | 1.80    | 4/5        |
      | +100      | 2.00    | 1/1        |
      | +120      | 2.20    | 6/5        |
      | +150      | 2.50    | 3/2        |
      | +200      | 3.00    | 2/1        |
      | +300      | 4.00    | 3/1        |
      | +500      | 6.00    | 5/1        |
      | +1000     | 11.00   | 10/1       |

  Scenario: Probability calculations verification
    When I create odds from decimal "1.25"
    Then the probability should be "80.00"
    When I create odds from decimal "1.67"
    Then the probability should be "59.88"
    When I create odds from decimal "3.33"
    Then the probability should be "30.03"
    When I create odds from decimal "6.67"
    Then the probability should be "14.99"

  Scenario: Decimal precision and rounding
    When I create odds from decimal "1.123"
    Then the decimal odds should be "1.12"
    When I create odds from decimal "1.126"
    Then the decimal odds should be "1.13"
    When I create odds from decimal "1.995"
    Then the decimal odds should be "2.00"
    When I create odds from decimal "1.994"
    Then the decimal odds should be "1.99"

  Scenario: Moneyline formatting edge cases
    When I create odds from moneyline "100.5"
    Then the moneyline odds should be "+100.50"
    When I create odds from moneyline "-100.5"
    Then the moneyline odds should be "-100.50"
    When I create odds from moneyline "100"
    Then the moneyline odds should be "+100"
    When I create odds from moneyline "-100"
    Then the moneyline odds should be "-100"

  Scenario: Very small probability odds
    When I create odds from decimal "1.01"
    Then the probability should be "99.01"
    When I create odds from decimal "1.001"
    Then the probability should be "100.00"

  Scenario: Round-trip conversion consistency
    When I create odds from decimal "2.75"
    Then the decimal odds should be "2.75"
    And the fractional odds should be "7/4"
    And the moneyline odds should be "+175"
    When I create odds from fractional 7/4
    Then the decimal odds should be "2.75"
    When I create odds from moneyline "+175"
    Then the decimal odds should be "2.75"
