Feature: Odds Ladder Integration
  As a developer using the odds formatter
  I want to use different odds ladders for fractional conversion
  So that I can get industry-standard fractional odds representations

  Scenario: Standard odds ladder conversion
    Given I have an odds factory with standard odds ladder
    When I create odds from decimal "1.50"
    Then the decimal odds should be "1.50"
    And the fractional odds should be "1/2"
    And the fractional odds should match the "standard" ladder expectations

  Scenario: Custom odds ladder with 1/1
    Given I have an odds factory with custom odds ladder
    When I create odds from decimal "2.00"
    Then the decimal odds should be "2.00"
    And the fractional odds should be "1/1"
    And the fractional odds should match the "custom" ladder expectations

  Scenario: Custom odds ladder below threshold
    Given I have an odds factory with custom odds ladder
    When I create odds from decimal "1.90"
    Then the decimal odds should be "1.90"
    And the fractional odds should be "1/1"
    And the fractional odds should match the "custom" ladder expectations

  Scenario: Utils odds ladder conversion
    Given I have an odds factory with utils odds ladder
    When I create odds from decimal "1.33"
    Then the decimal odds should be "1.33"
    And the fractional odds should be "1/3"
    And the fractional odds should match the "utils" ladder expectations

  Scenario: Standard ladder fallback for high odds
    Given I have an odds factory with standard odds ladder
    When I create odds from decimal "15.00"
    Then the decimal odds should be "15.00"
    And the fractional odds should be "14/1"

  Scenario: Custom ladder fallback for high odds
    Given I have an odds factory with custom odds ladder
    When I create odds from decimal "10.00"
    Then the decimal odds should be "10.00"
    And the fractional odds should be "9/1"

  Scenario Outline: Standard ladder threshold testing
    Given I have an odds factory with standard odds ladder
    When I create odds from decimal "<decimal>"
    Then the fractional odds should be "<fractional>"

    Examples:
      | decimal | fractional |
      | 1.02    | 1/50       |
      | 1.10    | 1/10       |
      | 1.25    | 1/4        |
      | 1.50    | 1/2        |
      | 2.00    | 1/1        |
      | 3.00    | 2/1        |
      | 5.00    | 4/1        |
      | 10.00   | 9/1        |

  Scenario Outline: Custom ladder threshold testing
    Given I have an odds factory with custom odds ladder
    When I create odds from decimal "<decimal>"
    Then the fractional odds should be "<fractional>"

    Examples:
      | decimal | fractional |
      | 1.20    | 1/5        |
      | 1.25    | 1/4        |
      | 1.33    | 1/3        |
      | 1.50    | 1/2        |
      | 2.00    | 1/1        |
      | 2.50    | 3/2        |
      | 3.00    | 2/1        |
      | 4.00    | 3/1        |
      | 5.00    | 4/1        |
      | 6.00    | 5/1        |

  Scenario: Default conversion without ladder
    Given I have an odds factory
    When I create odds from decimal "1.33"
    Then the decimal odds should be "1.33"
    And the fractional odds should be "33/100"

  Scenario: Comparison between different ladders
    Given I have an odds factory
    When I create odds from decimal "2.50"
    Then the fractional odds should be "3/2"
    When I reset the odds factory
    Given I have an odds factory with standard odds ladder
    When I create odds from decimal "2.50"
    Then the fractional odds should be "3/2"
    When I reset the odds factory
    Given I have an odds factory with custom odds ladder
    When I create odds from decimal "2.50"
    Then the fractional odds should be "3/2"
