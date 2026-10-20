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

namespace block_slider\privacy;

use block_slider\local\slides;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use core_privacy\tests\provider_testcase;

/**
 * Testy providera prywatności.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_slider\privacy\provider
 */
final class provider_test extends provider_testcase {
    /**
     * Slajder na kokpicie użytkownika.
     *
     * @param \stdClass $user właściciel kokpitu
     * @return \context_block
     */
    protected function create_dashboard_slider(\stdClass $user): \context_block {
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $block = $generator->create_instance([
            'parentcontextid' => \context_user::instance($user->id)->id,
            'pagetypepattern' => 'my-index',
            'title' => 'My slider',
        ]);
        $generator->create_slide(['blockinstanceid' => $block->id, 'title' => 'Dashboard slide', 'link' => '/my/']);
        return \context_block::instance($block->id);
    }

    /**
     * Opis przechowywanych danych.
     */
    public function test_get_metadata(): void {
        $collection = provider::get_metadata(new \core_privacy\local\metadata\collection('block_slider'));
        $this->assertCount(3, $collection->get_collection());
    }

    /**
     * Konteksty i użytkownicy: tylko kokpit właściciela.
     */
    public function test_contexts_and_users(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $context = $this->create_dashboard_slider($owner);
        $this->getDataGenerator()->get_plugin_generator('block_slider')->create_instance();

        $contextids = provider::get_contexts_for_userid($owner->id)->get_contextids();
        $this->assertSame([(int) $context->id], array_map('intval', $contextids));
        $this->assertEmpty(provider::get_contexts_for_userid($other->id)->get_contextids());

        $userlist = new userlist($context, 'block_slider');
        provider::get_users_in_context($userlist);
        $this->assertSame([(int) $owner->id], array_map('intval', $userlist->get_userids()));
    }

    /**
     * Eksport slajdów i grafik.
     */
    public function test_export_user_data(): void {
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $context = $this->create_dashboard_slider($owner);

        $this->export_context_data_for_user($owner->id, $context, 'block_slider');
        $writer = writer::with_context($context);
        $this->assertTrue($writer->has_any_data());
        $data = $writer->get_data([]);
        $this->assertSame('My slider', $data->title);
        $this->assertSame('Dashboard slide', $data->slides[0]->title);

        $list = slides::get_slides($context->instanceid);
        $slide = reset($list);
        $files = $writer->get_files([get_string('slides', 'block_slider'), $slide->id]);
        $this->assertArrayHasKey('slide1.png', $files);
    }

    /**
     * Usunięcie danych usuwa slajder z kokpitu, ale nie slajdery w kursach.
     */
    public function test_delete(): void {
        global $DB;
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $context = $this->create_dashboard_slider($owner);
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $course = $this->getDataGenerator()->create_course();
        $courseblock = $generator->create_instance(['parentcontextid' => \context_course::instance($course->id)->id]);
        $generator->create_slide(['blockinstanceid' => $courseblock->id]);

        provider::delete_data_for_all_users_in_context(\context_block::instance($courseblock->id));
        $this->assertTrue($DB->record_exists('block_instances', ['id' => $courseblock->id]));

        provider::delete_data_for_user(new approved_contextlist($owner, 'block_slider', [$context->id]));
        $this->assertFalse($DB->record_exists('block_instances', ['id' => $context->instanceid]));
        $this->assertEmpty(slides::get_slides($context->instanceid));
        $this->assertCount(1, slides::get_slides($courseblock->id));
    }

    /**
     * Usunięcie danych wybranych użytkowników w kontekście.
     */
    public function test_delete_data_for_users(): void {
        global $DB;
        $this->resetAfterTest();
        $owner = $this->getDataGenerator()->create_user();
        $context = $this->create_dashboard_slider($owner);

        provider::delete_data_for_users(new approved_userlist($context, 'block_slider', [$owner->id]));
        $this->assertFalse($DB->record_exists('block_instances', ['id' => $context->instanceid]));
    }
}
