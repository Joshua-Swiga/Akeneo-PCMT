Feature: Editing a 'select options' rule
  As a Catalog Manager
  When I click on a rule and send filled out form
  I want to be able to update a rule

  @javascript
  Scenario:
    Given I log in as a test user
    And I wait and follow link "Rules"
    And I filter rules to "test select options rule"
    And I wait and click edit on last rule
    And I wait
    And I click on select field no 1
    And I choose "MD COUNTRY" option
    And I click on select field no 2
    And I choose "MD - RECIPIENT MAPPING" option
    And I click on select field no 2
    And I choose "MD - SUPPLIER MAPPING" option
    And I save edit form
    Then I should get error message
    Then I wait
    And I click on select field no 3
    And I choose "UNIQUE ID" option
    And I click on select field no 4
    And I choose "MD SUPPLIER NAME" option
    And I save edit form
    Then I should get success message




    
