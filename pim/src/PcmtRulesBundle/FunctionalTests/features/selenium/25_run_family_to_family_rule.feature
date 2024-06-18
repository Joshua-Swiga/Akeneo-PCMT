Feature: Running a 'family to family' rule
  As a Catalog Manager
  When I click to run a rule
  I want to see that the rule was processed

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "Rules"
    And I filter rules to "test family to family rule"
    And I wait and click view on last rule
    And I click launch button
    Then first job on the list should be "test family to family rule" with status ">Completed<"
    When I click on first job
    Then I should see the "Attributes found" row with value "2"






    
