Feature: Approving Drafts
  In order to introduce the desired changes to the product
  As a Catalog Manager
  I want to be able to approve a draft that includes latest changes for the product

  @javascript
  Scenario:
    Given I log in as a test user
    And I wait and follow link "Products"
    And I wait and click edit on first product
    And I wait and click button "Edit as a draft"
    And I wait to load page ""
    And I wait 1 seconds
    And I wait and follow link "Activity"
    And I wait to load page "DASHBOARD"
    When I follow "Drafts"
    And I read number of drafts
    And I wait and click approve on first draft
    And I confirm approval
    Then the number of drafts should be lower by 1, try 2 times
