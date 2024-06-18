Feature: Bulk Approve
  In order to save time on approving drafts
  As a Catalog Manager
  I want to be able to select and bulk approve drafts

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
    And I select 1 draft checkboxes for mass action
    And I wait and follow link "Bulk approve"
    When I confirm approval
    Then the number of drafts should be lower by 1, try 4 times