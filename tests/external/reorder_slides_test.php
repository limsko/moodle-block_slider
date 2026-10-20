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

namespace block_slider\external;

use block_slider\local\slides;
use core_external\external_api;
use core_external\tests\externallib_testcase;

/**
 * Testy zapisu kolejności slajdów przez usługę.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(reorder_slides::class)]
final class reorder_slides_test extends externallib_testcase {
    /**
     * Blok w kursie z trzema slajdami.
     *
     * @return array [kurs, blok, slajdy]
     */
    protected function create_slider(): array {
        $course = $this->getDataGenerator()->create_course();
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $block = $generator->create_instance(['parentcontextid' => \context_course::instance($course->id)->id]);
        $created = [];
        foreach (['A', 'B', 'C'] as $title) {
            $created[] = $generator->create_slide(['blockinstanceid' => $block->id, 'title' => $title]);
        }
        return [$course, $block, $created];
    }

    /**
     * Nauczyciel z edycją zapisuje nową kolejność.
     */
    public function test_execute(): void {
        $this->resetAfterTest();
        [$course, $block, $created] = $this->create_slider();
        $this->setUser($this->getDataGenerator()->create_and_enrol($course, 'editingteacher'));

        $result = reorder_slides::execute($block->id, [$created[2]->id, $created[0]->id, $created[1]->id]);
        $result = external_api::clean_returnvalue(reorder_slides::execute_returns(), $result);

        $this->assertSame([(int) $created[2]->id, (int) $created[0]->id, (int) $created[1]->id], $result['slideids']);
        $this->assertSame(['C', 'A', 'B'], array_column(slides::get_slides($block->id), 'title'));
    }

    /**
     * Uczestnik nie może zmieniać kolejności.
     */
    public function test_execute_without_permission(): void {
        $this->resetAfterTest();
        [$course, $block, $created] = $this->create_slider();
        $this->setUser($this->getDataGenerator()->create_and_enrol($course, 'student'));

        $this->expectException(\required_capability_exception::class);
        reorder_slides::execute($block->id, [$created[2]->id, $created[0]->id]);
    }

    /**
     * Instancja innego bloku jest odrzucana.
     */
    public function test_execute_other_block(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $html = $this->getDataGenerator()->create_block('html');

        $this->expectException(\dml_missing_record_exception::class);
        reorder_slides::execute($html->id, [1]);
    }
}
