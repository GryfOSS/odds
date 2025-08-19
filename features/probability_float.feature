Feature: Probability Float Functionality
  As a developer using the odds formatter
  I want to access probability as a float value
  So that I can perform numerical comparisons and calculations

  Background:
    Given I have an odds factory

  Scenario: Basic probability float access
    When I create odds from decimal "2.00"
    Then the probability should be "50.00"
    And the probability float should be 50.0

  Scenario: Probability float precision
    When I create odds from decimal "1.50"
    Then the probability should be "66.67"
    And the probability float should be 66.67

  Scenario: Probability float comparisons - favorites
    When I create odds from decimal "1.25"
    Then the probability float should be greater than 75.0
    And the probability float should be less than 85.0

  Scenario: Probability float comparisons - underdogs
    When I create odds from decimal "4.00"
    Then the probability float should be greater than 20.0
    And the probability float should be less than 30.0

  Scenario: Edge case - minimum odds
    When I create odds from decimal "1.00"
    Then the probability float should be 100.0

  Scenario: Edge case - very low probability
    When I create odds from decimal "100.00"
    Then the probability float should be 1.0

  Scenario: Edge case - high precision
    When I create odds from decimal "3.33"
    Then the probability should be "30.03"
    And the probability float should be 30.03

  Scenario Outline: Multiple probability float validations
    When I create odds from decimal "<decimal>"
    Then the probability float should be <expected_float>

    Examples:
      | decimal | expected_float |
      | 1.01    | 99.01          |
      | 1.10    | 90.91          |
      | 1.33    | 75.19          |
      | 2.50    | 40.0           |
      | 5.00    | 20.0           |
      | 10.00   | 10.0           |

  Scenario: Probability float vs string consistency
    When I create odds from decimal "1.67"
    Then the probability should be "59.88"
    And the probability float should be 59.88

  Scenario: Comparison between different odds
    Given I create odds from decimal "1.50"
    Then the probability float should be greater than 60.0
    When I create odds from decimal "3.00"
    Then the probability float should be less than 40.0
