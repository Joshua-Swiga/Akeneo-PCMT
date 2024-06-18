Feature: Adding permissions
  In order limit access to Categories
  As a Catalog Manager
  I want to be able assign specific access levels for individual user.

  @javascript
  Scenario:
    Given I log in as a test user
    And I wait and follow link "Settings"
    And I wait and follow link "Categories"
    And I go to " Master and Mapping Data" category tree child
    And I wait and follow link "Permissions"
    And I edit permissions with parameters All All All
    And I save
    When I go to the products list for "Master and Mapping Data" category
    Then I should see more than zero products
    When I wait and follow link "Settings"
    And I wait and follow link "Categories"
    And I go to " Master and Mapping Data" category tree child
    And I wait and follow link "Permissions"
    When I edit permissions with parameters Redactor Redactor Redactor
    And I save
    Then I should see correct permissions set
    When I go to the products list for "Master and Mapping Data" category
    Then I should see 0 products on the list
    And I wait and follow link "Settings"
    And I wait and follow link "Categories"
    And I go to " Master and Mapping Data" category tree child
    And I wait and follow link "Permissions"
    And I edit permissions with parameters All All All
    And I save

    
