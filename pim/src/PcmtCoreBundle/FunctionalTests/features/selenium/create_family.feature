Feature: Create a family
  As a user
  I want to be able to create a new family

  @javascript
  Scenario:
    Given I log in as a test user
    And I go to the Settings page
    And I go to the Family page
    And I click on create family
    When I fill in the family code with the value
    And I click save button
    Then I should see the flash message with success
