Feature: Edit a family
  As a user
  I want to be able to edit existing family

  @javascript
  Scenario:
    Given I log in as a test user
    And I go to the Settings page
    And I go to the Family page
    And I click on the MD_HUB family
    When I add a French translation
    And I click the Save button
    Then I should see the flash message with success

