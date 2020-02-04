@javascript
Feature: Regenerate secret of a connection
  In order to get a new secret
  As an administrator
  I need to be able to regenerate secret of an existing connection

  Scenario: Successfully regenerate a new secret
    Given a "default" catalog configuration
    And the source Connection "Magento" has been created
    And I am logged in as "Peter"
    When I regenerate the secret of the "Magento" connection
    Then the secret should have changed
