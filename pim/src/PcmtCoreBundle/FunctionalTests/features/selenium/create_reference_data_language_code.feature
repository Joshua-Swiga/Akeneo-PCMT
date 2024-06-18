Feature: Reference data simple select attribute LanguageCode
  In order to have reference data attributes
  As Catalog Manager
  I want to be able to create reference data simple select attribute

  @javascript
  Scenario:
    Given I log in as a test user
    When I wait and follow link "Settings"
    When I wait to load page "ATTRIBUTES"
    And I press the "Create Attribute" button
    When I select "Reference data simple select" on modal
    When I fill in "code" with "reference_data_simple_select_test"
    And I follow "Choose the attribute group"
    When I choose "_PRODUCT" option
    And I follow "Choose the reference data type"
    When I choose "LanguageCode" option
    And I save
    When I wait to load page "ATTRIBUTES"
    When I wait and follow link "Settings"
    When I wait to load page "ATTRIBUTES"
    Then I should see text matching "[reference_data_simple_select_test]"
    Then I should delete created attribute
    When I wait to load page "ATTRIBUTES"