Feature: Deleting a 'pull images' rule
  As a Catalog Manager
  When I click to delete the rule
  I want to have it removed

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "Rules"
    And I filter rules to "test pull images rule"
    And I read number of rules
    And I wait and click delete on last rule
    And I confirm delete
    Then the number of rules should be lower by 1



    
