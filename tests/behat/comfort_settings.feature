@local @local_comfortmode
Feature: People choose their own comfort settings
  In order to use the site comfortably
  As a user
  I need to choose how the site looks and feels for me

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam       | Student  | student1@example.com |

  Scenario: A person saves comfort settings and goes back to the site defaults
    Given I log in as "student1"
    And I visit "/local/comfortmode/index.php"
    When I set the following fields to these values:
      | Text size      | Larger |
      | Easier reading | 1      |
      | Focus mode     | 1      |
    And I press "Save changes"
    Then I should see "Your comfort settings are saved."
    And "html[data-comfortmode-textsize='130']" "css_element" should exist
    And "html[data-comfortmode-easyread='1']" "css_element" should exist
    And "html[data-comfortmode-focus='1']" "css_element" should exist
    And "html[data-comfortmode-reducemotion]" "css_element" should not exist
    And I press "Use the site defaults"
    And I should see "Your comfort settings are back to the site defaults."
    And "html[data-comfortmode-textsize]" "css_element" should not exist
    And "html[data-comfortmode-focus]" "css_element" should not exist

  Scenario: The Preferences page links to the Comfort Mode page
    Given I log in as "student1"
    When I follow "Preferences" in the user menu
    And I follow "Comfort Mode"
    Then I should see "Choose how this site looks and feels for you."

  Scenario: A setting the site does not offer is not shown
    Given the following config values are set as admin:
      | enable_focus | 0 | local_comfortmode |
    And I log in as "student1"
    When I visit "/local/comfortmode/index.php"
    Then I should see "Text size"
    And I should not see "Focus mode"

  Scenario: A site default applies until a person chooses
    Given the following config values are set as admin:
      | default_textsize | 115 | local_comfortmode |
    When I log in as "student1"
    Then "html[data-comfortmode-textsize='115']" "css_element" should exist
    And I visit "/local/comfortmode/index.php"
    And I set the field "Text size" to "Standard"
    And I press "Save changes"
    And "html[data-comfortmode-textsize]" "css_element" should not exist
