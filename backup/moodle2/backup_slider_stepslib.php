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

/**
 * Kroki kopii zapasowej bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Struktura slajdów w kopii zapasowej.
 *
 * @package     block_slider
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_slider_block_structure_step extends backup_block_structure_step {
    /**
     * Definicja struktury.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $slider = new backup_nested_element('slider', ['id'], null);
        $slides = new backup_nested_element('slides');
        $slide = new backup_nested_element('slide', ['id'], [
            'title', 'subtitle', 'description', 'link', 'newwindow', 'buttontext', 'alttext', 'sortorder', 'visible',
            'timecreated', 'timemodified',
        ]);

        $slider->add_child($slides);
        $slides->add_child($slide);

        $slider->set_source_array([(object) ['id' => $this->task->get_blockid()]]);
        $slide->set_source_table('block_slider_slides', ['blockinstanceid' => backup::VAR_BLOCKID], 'sortorder ASC');

        $slide->annotate_files('block_slider', 'slide', 'id');
        $slide->annotate_files('block_slider', 'slidemobile', 'id');

        return $this->prepare_block_structure($slider);
    }
}
