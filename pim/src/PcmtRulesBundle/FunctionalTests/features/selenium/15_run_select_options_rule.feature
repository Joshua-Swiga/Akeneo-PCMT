Feature: Running a 'select options' rule
  As a Catalog Manager
  When I click to run a rule
  I want to see that the rule was processed

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "Rules"
    And I filter rules to "test select options rule"
    And I wait and click view on last rule
    And I click launch button
    Then first job on the list should be "test select options rule" with status ">Completed<"
    When I click on first job
    Then I should see the "Parameters" row with value "sourceFamily : MD_SUPPLIER_MAPPING, attributeCode : sku, attributeValue : MD_SUPPLIER_MASTER_SUPPLIERNAME, user_to_notify : admin, destinationAttribute : MD_HUB_COUNTRYISOCODE"






    
