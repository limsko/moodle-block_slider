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
 * Kroki odtwarzania bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Odtwarzanie struktury slajdów.
 *
 * @package     block_slider
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_slider_block_structure_step extends restore_structure_step {
    /**
     * Ścieżki elementów.
     *
     * @return restore_path_element[]
     */
    protected function define_structure() {
        return [
            new restore_path_element('block', '/block', true),
            new restore_path_element('slider', '/block/slider'),
            new restore_path_element('slide', '/block/slider/slides/slide'),
        ];
    }

    /**
     * Tworzy slajdy odtworzonego bloku.
     *
     * @param array $data dane bloku
     */
    public function process_block($data) {
        global $DB;

        $data = (object) $data;
        $blockid = $this->task->get_blockid();
        if (!$blockid || empty($data->slider['slides']['slide'])) {
            return;
        }

        foreach ($data->slider['slides']['slide'] as $slide) {
            $slide = (object) $slide;
            $oldid = $slide->id;
            unset($slide->id);
            $slide->blockinstanceid = $blockid;
            $newid = $DB->insert_record('block_slider_slides', $slide);
            $this->set_mapping('block_slider_slide', $oldid, $newid, true);
        }
    }

    /**
     * Odtwarza grafiki z identyfikatorami nowych slajdów.
     */
    protected function after_execute() {
        $this->add_related_files('block_slider', 'slide', 'block_slider_slide');
        $this->add_related_files('block_slider', 'slidemobile', 'block_slider_slide');
    }
}
