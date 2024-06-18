Feature: API GET requests for products

  Scenario: List products without permission
    Given I receive token
    When I send a "GET" request to "/api/rest/v1/products?with_count=true" endpoint
    Then The response status code should be 200
    And The number of results should be lower than 1400

  Scenario: List products with permission
    Given I receive token
    And I clear fixtures
    When I send a "GET" request to "/api/rest/v1/products?with_count=true" endpoint
    Then The response status code should be 200
    And The number of results should be greater than 2300

  Scenario: Get one product with permission
    Given I receive token
    When I send a "GET" request to "/api/rest/v1/products/101287" endpoint
    Then The response status code should be 200

  Scenario: Get one product without permission
    Given I receive token
    When I send a "GET" request to "/api/rest/v1/products/R_651" endpoint
    Then The response status code should be 403

