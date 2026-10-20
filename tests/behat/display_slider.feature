@block @block_slider @javascript
Feature: Display the slider block
  In order to see promoted content
  As a user
  I need to browse slides with the slider controls

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
      | blockname | contextlevel | reference | pagetypepattern | defaultregion | title   | effect | speed | autoplay |
      | slider    | Course       | C1        | course-view-*   | side-pre      | Banners | fade   | 0     | 0        |
    And the following "block_slider > slides" exist:
      | block   | title  | link              | image                                   |
      | Banners | First  | /course/index.php | blocks/slider/tests/fixtures/slide1.png |
      | Banners | Second |                   | blocks/slider/tests/fixtures/slide2.png |
      | Banners | Third  |                   | blocks/slider/tests/fixtures/slide3.png |

  Scenario: Arrows and pagination change the visible slide
    Given I am on the "C1" "Course" page logged in as "student1"
    Then I should see "First" in the "Banners" "block"
    And I should not see "Second" in the "Banners" "block"
    And "Go to slide 3" "button" should exist in the "Banners" "block"
    When I click on "Next slide" "button" in the "Banners" "block"
    Then I should see "Second" in the "Banners" "block"
    And I should not see "First" in the "Banners" "block"
    And I click on "Go to slide 3" "button" in the "Banners" "block"
    And I should see "Third" in the "Banners" "block"
    And I click on "Next slide" "button" in the "Banners" "block"
    And I should see "First" in the "Banners" "block"
    And I click on "Previous slide" "button" in the "Banners" "block"
    And I should see "Third" in the "Banners" "block"

  Scenario: Slide with a link opens the target page
    Given I am on the "C1" "Course" page logged in as "student1"
    When I click on "First" "block_slider > Slider slide"
    Then the url should match "/course/index\.php"

  Scenario: Teacher changes the display settings
    Given I am on the "C1" "Course" page logged in as "teacher1"
    And I turn editing mode on
    When I configure the "Banners" block
    And I set the following fields to these values:
      | Arrows        | 0 |
      | Autoplay      | 1 |
      | Show captions | 0 |
    And I press "Save changes"
    And I turn editing mode off
    Then "Next slide" "button" should not exist in the "Banners" "block"
    And "Pause slideshow" "button" should exist in the "Banners" "block"
    And I should not see "First" in the "Banners" "block"
    And I click on "Pause slideshow" "button" in the "Banners" "block"
    And "Play slideshow" "button" should exist in the "Banners" "block"
