@javascript
Feature: Filter products by number field
  In order to filter products by number attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  @acceptance-front
  Scenario: Successfully filter products by empty value for number attributes
    Given the following attributes:
      | label-en_US | type               | localizable | scopable | useable_as_grid_filter | decimals_allowed | negative_allowed | group | code  |
      | count       | pim_catalog_number | 0           | 0        | 1                      | 0                | 0                | other | count |
      | rate        | pim_catalog_number | 0           | 0        | 1                      | 1                | 0                | other | rate  |
    And the following products:
      | sku    | count | rate |
      | postit | 200   |      |
      | book   |       | 9.5  |
      | mug    |       |      |
    And I am on the products grid
    Then the grid should contain 3 elements
    And I should see products "postit, book, mug"
    And I should be able to use the following filters:
      | filter | operator     | value | result |
      | count  | is empty     |       |        |
      | count  | is not empty |       | postit |
      | count  | >            | 200   |        |
      # | count  | <            | 200   |        |
      # | count  | >            | 199   | postit |
      # | count  | <            | 201   | postit |
      # | count  | >=           | 200   | postit |
      # | count  | <=           | 200   | postit |
      # | count  | =            | 200   | postit |
      # | count  | =            | 0     |        |
      # | count  | >            | 0     | postit |
      # | rate   | is empty     |       |        |
      # | rate   | is not empty |       | book   |
      # | rate   | >            | 9.5   |        |
      # | rate   | <=           | 9.5   | book   |
      # | rate   | =            | 0     |        |
      # | rate   | >            | 0     | book   |
