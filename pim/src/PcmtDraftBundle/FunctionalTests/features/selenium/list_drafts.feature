Feature: List of Drafts
  In order to make operations on drafts
  As a privileged user
  I want to be able to access the list of drafts

  @javascript
  Scenario: Verify user is able to search across the site
    Given I log in as a test user
    When I wait and follow link "Drafts"
    And I wait to load page "DRAFTS"
    Then I should see text matching "draft"

