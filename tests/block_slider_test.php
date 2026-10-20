<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace block_slider;

use block_slider\local\slides;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/moodleblock.class.php');
require_once($CFG->dirroot . '/blocks/slider/block_slider.php');

/**
 * Testy klasy bloku.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_slider
 */
final class block_slider_test extends \advanced_testcase {
    /**
     * Strona kursu, na której wyświetlany jest blok.
     *
     * @param \stdClass $course kurs
     * @return \moodle_page
     */
    protected function course_page(\stdClass $course): \moodle_page {
        $page = new \moodle_page();
        $page->set_url('/course/view.php', ['id' => $course->id]);
        $page->set_course($course);
        return $page;
    }

    /**
     * Instancja bloku w kursie z ustawieniami.
     *
     * @param \stdClass $course kurs
     * @param array $config ustawienia
     * @return \block_slider
     */
    protected function create_block(\stdClass $course, array $config = []): \block_slider {
        $record = ['parentcontextid' => \context_course::instance($course->id)->id, 'pagetypepattern' => 'course-view-*'];
        $instance = $this->getDataGenerator()->get_plugin_generator('block_slider')->create_instance($record, $config);
        return block_instance('slider', $instance, $this->course_page($course));
    }

    /**
     * Uczestnik widzi slajdy bez przycisku zarządzania.
     */
    public function test_content_for_student(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $block = $this->create_block($course, ['title' => 'Banners']);
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $generator->create_slide(['blockinstanceid' => $block->instance->id, 'title' => 'Visible slide']);
        $hidden = $generator->create_slide(['blockinstanceid' => $block->instance->id, 'title' => 'Hidden slide']);
        slides::set_visible($hidden, false);

        $this->setUser($student);
        $content = $block->get_content();

        $this->assertStringContainsString('Visible slide', $content->text);
        $this->assertStringNotContainsString('Hidden slide', $content->text);
        $this->assertSame('', $content->footer);
        $this->assertSame('Banners', $block->title);
        $this->assertFalse($block->hide_header());
    }

    /**
     * Pusty blok jest niewidoczny dla uczestnika, a w trybie edycji pokazuje podpowiedź i link.
     */
    public function test_empty_content(): void {
        global $USER;
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $block = $this->create_block($course);

        $this->setUser($student);
        $this->assertSame('', $block->get_content()->text);
        $this->assertTrue($block->hide_header());

        $this->setAdminUser();
        $USER->editing = 1;
        $block = block_instance('slider', $block->instance, $this->course_page($course));
        $content = $block->get_content();
        $this->assertStringContainsString(get_string('noslides', 'block_slider'), $content->text);
        $this->assertStringContainsString('/blocks/slider/manage.php?blockid=' . $block->instance->id, $content->footer);
        $this->assertStringContainsString('returnurl=', $content->footer);
        $this->assertFalse($block->hide_header());
    }

    /**
     * Klasy widoczności na urządzeniach i bez ramki.
     */
    public function test_html_attributes(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $block = $this->create_block($course, ['device' => 1, 'frameless' => 1]);
        $classes = $block->html_attributes()['class'];
        $this->assertStringContainsString('d-none d-md-block', $classes);
        $this->assertStringContainsString('block-slider-frameless', $classes);

        $block = $this->create_block($course, ['device' => 2]);
        $this->assertStringContainsString('d-md-none', $block->html_attributes()['class']);
    }

    /**
     * Usunięcie instancji usuwa slajdy, a kopiowanie je powiela.
     */
    public function test_instance_delete_and_copy(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $source = $this->create_block($course);
        $generator->create_slide(['blockinstanceid' => $source->instance->id, 'title' => 'A']);
        $generator->create_slide(['blockinstanceid' => $source->instance->id, 'title' => 'B']);

        $target = $this->create_block($course);
        $this->assertTrue($target->instance_copy($source->instance->id));
        $this->assertSame(['A', 'B'], array_column(slides::get_slides($target->instance->id), 'title'));

        blocks_delete_instance($source->instance);
        $this->assertEmpty(slides::get_slides($source->instance->id));
        $this->assertCount(2, slides::get_slides($target->instance->id));
    }

    /**
     * Ustawienia dla usług sieciowych mają komplet wartości.
     */
    public function test_config_for_external(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $block = $this->create_block($course, ['interval' => 8000]);

        $config = $block->get_config_for_external();
        $this->assertSame(8000, $config->instance->interval);
        $this->assertTrue(property_exists($config->instance, 'captionstyle'));
    }
}
