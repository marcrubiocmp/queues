Feature: Domain Events

  Scenario: I send and consume a domain event
    Given I send a random domain event
    Then I should consume the random domain event

  Scenario: I send, but not consume a domain event from an unwanted topic
    Given I send a random domain event with an unwanted topic
    Then I should not consume the random domain event