Feature: Basic Odds Conversion
  As a developer using the odds formatter
  I want to convert between different odds formats
  So that I can work with odds in the format most suitable for my application

  Background:
    Given I have an odds factory

  Scenario: Convert decimal to fractional and moneyline (1/1)
    When I create odds from decimal "2.00"
    Then the decimal odds should be "2.00"
    And the fractional odds should be "1/1"
    And the moneyline odds should be "+100"
    And the probability should be "50.00"
    And all values should be strings

  Scenario: Convert decimal to fractional and moneyline (favorites)
    When I create odds from decimal "1.50"
    Then the decimal odds should be "1.50"
    And the fractional odds should be "1/2"
    And the moneyline odds should be "-200"
    And the probability should be "66.67"

  Scenario: Convert decimal to fractional and moneyline (underdogs)
    When I create odds from decimal "3.00"
    Then the decimal odds should be "3.00"
    And the fractional odds should be "2/1"
    And the moneyline odds should be "+200"
    And the probability should be "33.33"

  Scenario: Convert fractional to decimal and moneyline
    When I create odds from fractional 3/2
    Then the decimal odds should be "2.50"
    And the fractional odds should be "3/2"
    And the moneyline odds should be "+150"
    And the probability should be "40.00"

  Scenario: Convert moneyline positive to decimal and fractional
    When I create odds from moneyline "+150"
    Then the decimal odds should be "2.50"
    And the fractional odds should be "3/2"
    And the moneyline odds should be "+150"
    And the probability should be "40.00"

  Scenario: Convert moneyline negative to decimal and fractional
    When I create odds from moneyline "-200"
    Then the decimal odds should be "1.50"
    And the fractional odds should be "1/2"
    And the moneyline odds should be "-200"
    And the probability should be "66.67"

  Scenario: Convert even moneyline to decimal and fractional
    When I create odds from moneyline "0"
    Then the decimal odds should be "1.00"
    And the fractional odds should be "0/1"
    And the moneyline odds should be "0"
    And the probability should be "100.00"

  Scenario: Decimal normalization
    When I create odds from decimal "2.5"
    Then the decimal odds should be "2.50"
    And the fractional odds should be "3/2"
    And the moneyline odds should be "+150"

  Scenario: High odds conversion
    When I create odds from decimal "10.00"
    Then the decimal odds should be "10.00"
    And the fractional odds should be "9/1"
    And the moneyline odds should be "+900"
    And the probability should be "10.00"
