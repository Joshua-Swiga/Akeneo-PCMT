Feature: Create concatenated attribute
  In order to have data about one or more attributes
  As Catalog Manager
  I want to be able to create concatenated attribute

  @javascript
  Scenario:
    Given I log in as a test user
    When  I follow "Settings"
    And wait for the page to load
    And I press the "Create Attribute" button
    And wait for the page to load
    When I select "Concatenated Attributes" on modal
    And wait for the page to load
    When I fill in "code" with "concatenated_attribute_test"
    And I follow "Choose the attribute group"
    When I choose "_ITEM OPTIONS" option
    And I choose "STRENGTH" "DOSAGE_FORM" "ACTIVE_INGREDIENT" attributes for concatenated fields
    When I fill in "separator1" with "###"
    And I fill in "separator2" with "$$$"
    And I save
    And wait for the page to load
    When I follow "Attributes"
    And wait for the page to load
    Then I should see text matching "[concatenated_attribute_test]"
    Then I should delete created attribute