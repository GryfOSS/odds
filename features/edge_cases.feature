Feature: Edge Cases and Error Handling
  As a developer using the odds formatter
  I want the library to handle edge cases gracefully
  So that my application can handle invalid input appropriately

  Background:
    Given I have an odds factory

  Scenario: Invalid decimal odds - below minimum
    When I create odds from decimal "0.5"
    Then an InvalidPriceException should be thrown with message "Invalid decimal value provided: 0.5. Min value: 1.0"

  Scenario: Invalid decimal odds - zero
    When I create odds from decimal "0"
    Then an InvalidPriceException should be thrown with message "Invalid decimal value provided: 0. Min value: 1.0"

  Scenario: Invalid decimal odds - negative
    When I create odds from decimal "-1.5"
    Then an InvalidPriceException should be thrown with message "Invalid decimal value provided: -1.5. Min value: 1.0"

  Scenario: Invalid decimal odds - non-numeric
    When I create odds from decimal "abc"
    Then an InvalidPriceException should be thrown with message "Invalid decimal value provided: abc. Min value: 1.0"

  Scenario: Invalid fractional odds - negative numerator
    When I create odds from fractional -1/2
    Then an InvalidPriceException should be thrown with message "Invalid numerator provided"

  Scenario: Invalid fractional odds - zero denominator
    When I create odds from fractional 1/0
    Then an InvalidPriceException should be thrown with message "Invalid denominator provided"

  Scenario: Invalid fractional odds - negative denominator
    When I create odds from fractional 1/-1
    Then an InvalidPriceException should be thrown with message "Invalid denominator provided"

  Scenario: Invalid moneyline odds - non-numeric
    When I create odds from moneyline "abc"
    Then an InvalidPriceException should be thrown with message "Invalid moneyline value provided: abc"

  Scenario: Minimum valid decimal odds
    When I create odds from decimal "1.00"
    Then the decimal odds should be "1.00"
    And the fractional odds should be "0/1"
    And the moneyline odds should be "0"
    And the probability should be "100.00"

  Scenario: Direct Odds construction with invalid decimal
    When I directly create odds with decimal "0.5", fractional "1/2" and moneyline "+100"
    Then an InvalidPriceException should be thrown with message "Invalid decimal value provided: 0.5. Min value: 1.0"

  Scenario: Very high decimal odds
    When I create odds from decimal "100.00"
    Then the decimal odds should be "100.00"
    And the fractional odds should be "99/1"
    And the moneyline odds should be "+9900"
    And the probability should be "1.00"

  Scenario: Zero fractional numerator
    When I create odds from fractional 0/1
    Then the decimal odds should be "1.00"
    And the fractional odds should be "0/1"
    And the moneyline odds should be "0"
    And the probability should be "100.00"

  Scenario: Large fractional numbers
    When I create odds from fractional 100/1
    Then the decimal odds should be "101.00"
    And the fractional odds should be "100/1"
    And the moneyline odds should be "+10000"

  Scenario: Decimal rounding precision
    When I create odds from decimal "2.005"
    Then the decimal odds should be "2.01"

  Scenario: Decimal rounding down
    When I create odds from decimal "2.004"
    Then the decimal odds should be "2.00"
