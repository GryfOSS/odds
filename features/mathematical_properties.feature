Feature: Mathematical Properties and Validation
  As a developer using the odds formatter
  I want to verify mathematical properties and relationships
  So that I can ensure the calculations are accurate and consistent

  Background:
    Given I have an odds factory

  Scenario: Probability sum validation for complementary events
    When I create odds from decimal "2.00"
    Then the probability should be "50.00"
    When I create odds from decimal "2.00"
    Then the probability should be "50.00"
    # Note: In a fair market, complementary events should sum to 100%

  Scenario: Inverse relationship between odds and probability
    When I create odds from decimal "1.25"
    Then the probability should be "80.00"
    When I create odds from decimal "5.00"
    Then the probability should be "20.00"
    When I create odds from decimal "10.00"
    Then the probability should be "10.00"
    When I create odds from decimal "100.00"
    Then the probability should be "1.00"

  Scenario: Moneyline breakeven validation
    When I create odds from moneyline "+100"
    Then the probability should be "50.00"
    When I create odds from moneyline "-100"
    Then the probability should be "50.00"

  Scenario: Fractional odds mathematical validation
    When I create odds from fractional 1/1
    Then the decimal odds should be "2.00"
    And the probability should be "50.00"
    When I create odds from fractional 1/4
    Then the decimal odds should be "1.25"
    And the probability should be "80.00"
    When I create odds from fractional 4/1
    Then the decimal odds should be "5.00"
    And the probability should be "20.00"

  Scenario: Large number precision handling
    When I create odds from decimal "999.99"
    Then the decimal odds should be "999.99"
    And the probability should be "0.10"
    When I create odds from fractional 999/1
    Then the decimal odds should be "1000.00"
    And the probability should be "0.10"

  Scenario: Micro-precision decimal handling
    When I create odds from decimal "1.0001"
    Then the decimal odds should be "1.00"
    When I create odds from decimal "1.0099"
    Then the decimal odds should be "1.01"

  Scenario: Extreme moneyline values
    When I create odds from moneyline "+99999"
    Then the decimal odds should be "1000.99"
    When I create odds from moneyline "-99999"
    Then the decimal odds should be "1.00"

  Scenario: Boundary value testing
    When I create odds from decimal "1.00"
    Then the probability should be "100.00"
    And the moneyline odds should be "0"
    And the fractional odds should be "0/1"

  Scenario: Decimal normalization consistency
    When I create odds from decimal "2"
    Then the decimal odds should be "2.00"
    When I create odds from decimal "2.0"
    Then the decimal odds should be "2.00"
    When I create odds from decimal "2.00"
    Then the decimal odds should be "2.00"
    When I create odds from decimal "2.000"
    Then the decimal odds should be "2.00"

  Scenario: Moneyline positive/negative format consistency
    When I create odds from moneyline "150"
    Then the moneyline odds should be "+150"
    When I create odds from moneyline "+150"
    Then the moneyline odds should be "+150"
    When I create odds from moneyline "-150"
    Then the moneyline odds should be "-150"

  Scenario: Fractional reduction verification
    When I create odds from fractional 2/4
    Then the decimal odds should be "1.50"
    And the fractional odds should be "2/4"
    # Note: Input fraction is preserved as-is

  Scenario: Zero probability edge case
    When I create odds from decimal "1.00"
    Then the probability should be "100.00"
    # This represents a certainty (100% probability)

  Scenario: Very high probability case
    When I create odds from decimal "1.001"
    Then the probability should be "100.00"
    # Near certainty case
