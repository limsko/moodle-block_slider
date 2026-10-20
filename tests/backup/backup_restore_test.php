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

namespace block_slider\backup;

use backup;
use backup_controller;
use block_slider\local\slides;
use restore_controller;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

/**
 * Testy kopii zapasowej i odtwarzania slajdów.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \backup_slider_block_task
 * @covers      \backup_slider_block_structure_step
 * @covers      \restore_slider_block_task
 * @covers      \restore_slider_block_structure_step
 */
final class backup_restore_test extends \advanced_testcase {
    /**
     * Slajdy, kolejność, widoczność i grafiki trafiają do kopii kursu.
     */
    public function test_backup_and_restore_course(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $block = $generator->create_instance([
            'parentcontextid' => \context_course::instance($course->id)->id,
            'pagetypepattern' => 'course-view-*',
            'title' => 'Banners',
        ]);
        $generator->create_slide(['blockinstanceid' => $block->id, 'title' => 'First', 'link' => '/my/']);
        $second = $generator->create_slide([
            'blockinstanceid' => $block->id,
            'title' => 'Second',
            'image' => __DIR__ . '/../fixtures/slide2.png',
            'mobileimage' => __DIR__ . '/../fixtures/mobile.png',
        ]);
        slides::set_visible($second, false);

        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        $newcourse = $this->getDataGenerator()->create_course();
        $rc = new restore_controller(
            $backupid,
            $newcourse->id,
            backup::INTERACTIVE_NO,
            backup::MODE_IMPORT,
            $USER->id,
            backup::TARGET_EXISTING_ADDING
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        $blocks = $DB->get_records('block_instances', [
            'blockname' => 'slider',
            'parentcontextid' => \context_course::instance($newcourse->id)->id,
        ]);
        $this->assertCount(1, $blocks);
        $restored = reset($blocks);
        $this->assertNotEquals($block->id, $restored->id);

        $list = array_values(slides::get_slides($restored->id));
        $this->assertSame(['First', 'Second'], array_column($list, 'title'));
        $this->assertSame('/my/', $list[0]->link);
        $this->assertEquals(0, $list[1]->visible);

        $images = slides::get_images(\context_block::instance($restored->id));
        $this->assertSame('slide1.png', $images[$list[0]->id][slides::AREA_IMAGE]->get_filename());
        $this->assertSame('slide2.png', $images[$list[1]->id][slides::AREA_IMAGE]->get_filename());
        $this->assertSame('mobile.png', $images[$list[1]->id][slides::AREA_MOBILE]->get_filename());
    }
}
