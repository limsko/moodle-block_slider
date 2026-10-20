@block @block_slider
Feature: Manage slides of the slider block
  In order to promote content on a page
  As a teacher
  I need to add, edit, reorder, hide and delete slides

  Background:
    Given the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | One      |
      | student1 | Student   | One      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "blocks" exist:
      | blockname | contextlevel | reference | pagetypepattern | defaultregion | title   |
      | slider    | Course       | C1        | course-view-*   | side-pre      | Banners |

  @javascript @_file_upload
  Scenario: Teacher adds, edits and deletes a slide
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    When I click on "Manage slides" "link" in the "Banners" "block"
    Then I should see "There are no slides yet. Add the first slide."
    And I click on "Add slide" "link"
    And I upload "blocks/slider/tests/fixtures/slide1.png" file to "Image" filemanager
    And I set the following fields to these values:
      | Title    | Welcome           |
      | Subtitle | New courses       |
      | Link     | /course/index.php |
    And I press "Add slide"
    And I should see "The slide has been saved."
    And I should see "Welcome" in the "Welcome" "block_slider > Slide"
    And I should see "New courses" in the "Welcome" "block_slider > Slide"
    And I click on "Edit" "link" in the "Welcome" "block_slider > Slide"
    And the field "Title" matches value "Welcome"
    And I set the field "Title" to "Welcome again"
    And I press "Save changes"
    And I should see "Welcome again" in the "Welcome again" "block_slider > Slide"
    And I click on "Delete" "link" in the "Welcome again" "block_slider > Slide"
    And I should see "Are you sure you want to delete this slide?"
    And I press "Continue"
    And I should see "The slide has been deleted."
    And I should see "There are no slides yet. Add the first slide."

  Scenario: Image and link are validated
    Given I am on the "Banners" "block_slider > Manage slides" page logged in as "teacher1"
    And I click on "Add slide" "link"
    When I set the field "Link" to "javascript:alert(1)"
    And I press "Add slide"
    Then I should see "Enter a full address starting with http:// or https://, or a path on this site starting with /."
    And I should see "Required"

  Scenario: Teacher changes the order of slides with arrows
    Given the following "block_slider > slides" exist:
      | block   | title  |
      | Banners | First  |
      | Banners | Second |
      | Banners | Third  |
    And I am on the "Banners" "block_slider > Manage slides" page logged in as "teacher1"
    When I click on "Move down" "link" in the "First" "block_slider > Slide"
    Then "Second" "block_slider > Slide" should appear before "First" "block_slider > Slide"
    And "First" "block_slider > Slide" should appear before "Third" "block_slider > Slide"
    And I click on "Move up" "link" in the "Third" "block_slider > Slide"
    And "Third" "block_slider > Slide" should appear before "First" "block_slider > Slide"

  @javascript
  Scenario: Teacher moves a slide with the accessible move dialogue
    Given the following "block_slider > slides" exist:
      | block   | title  |
      | Banners | First  |
      | Banners | Second |
      | Banners | Third  |
    And I am on the "Banners" "block_slider > Manage slides" page logged in as "teacher1"
    When I click on "Move First" "button" in the "First" "block_slider > Slide"
    And I click on "After \"Third\"" "link" in the "Move First" "dialogue"
    Then "Third" "block_slider > Slide" should appear before "First" "block_slider > Slide"
    And I reload the page
    And "Second" "block_slider > Slide" should appear before "Third" "block_slider > Slide"
    And "Third" "block_slider > Slide" should appear before "First" "block_slider > Slide"

  Scenario: Hidden slides are not shown to students
    Given the following "block_slider > slides" exist:
      | block   | title   |
      | Banners | Public  |
      | Banners | Private |
    And I am on the "Banners" "block_slider > Manage slides" page logged in as "teacher1"
    When I click on "Hide" "link" in the "Private" "block_slider > Slide"
    Then I should see "Hidden from students" in the "Private" "block_slider > Slide"
    And I should not see "Hidden from students" in the "Public" "block_slider > Slide"
    And I log out
    And I am on the "C1" "Course" page logged in as "student1"
    And I should see "Public" in the "Banners" "block"
    And I should not see "Private" in the "Banners" "block"
    And I should not see "Manage slides"
