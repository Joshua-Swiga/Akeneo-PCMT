Feature: Editing a 'pull images' rule
  As a Catalog Manager
  When I click on a rule and send filled out form
  I want to be able to update a rule

  @javascript
  Scenario:
    Given I log in as a test user
    And I wait and follow link "Rules"
    And I filter rules to "test pull images rule"
    And I wait and click edit on last rule
    And I wait
    When I click on select field no 1
    And I choose "MD - RECIPIENT MAPPING" option
    And I save edit form
    Then I should get error message
    When I click on select field no 1
    And I choose "IMAGE FAMILY" option
    And I wait
    And I click on select field no 2
    And I choose the first option
    And I click on select field no 3
    And I choose the first option
    And I save edit form
    Then I should get success message




    
