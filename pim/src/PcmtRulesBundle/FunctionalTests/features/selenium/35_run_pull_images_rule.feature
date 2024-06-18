Feature: Running a 'pull images' rule
  As a Catalog Manager
  When I click to run a rule
  I want to see that the rule was processed

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "Rules"
    And I filter rules to "test pull images rule"
    And I wait and click view on last rule
    And I click launch button
    Then first job on the list should be "test pull images rule" with status ">Completed<"
    When I click on first job
    Then I should see the "Parameters" row with value "family : IMAGE_FAMILY, user_to_notify : admin, sourceAttribute : URL_ATTRIBUTE, destinationAttribute : IMAGE_ATTRIBUTE"






    
