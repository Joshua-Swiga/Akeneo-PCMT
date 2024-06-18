Feature: Bulk delete of products
  In order delete products fast
  As a Catalog Manager
  I want to be able to delete them in bulk mode

  @javascript
  Scenario:
    Given I log in as a test user
    And I follow "Products"
    And wait for the page to load
    And I read number of products
    When I check 2 products
    And I click delete
    And I confirm delete
    And wait for the page to load
    Then the number of results should be lower by 2, try 3 times