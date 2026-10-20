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

namespace block_slider\output;

use plugin_renderer_base;

/**
 * Renderer bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {
    /**
     * Slajder z inicjalizacją modułu JS.
     *
     * @param slider $slider slajder
     * @return string
     */
    protected function render_slider(slider $slider): string {
        $data = $slider->export_for_template($this);
        if (empty($data['hasslides'])) {
            return '';
        }
        $this->page->requires->js_call_amd('block_slider/slider', 'init', ['#' . $slider->get_uniqueid()]);
        return $this->render_from_template('block_slider/slider', $data);
    }

    /**
     * Panel zarządzania slajdami.
     *
     * @param manage_page $page panel
     * @return string
     */
    protected function render_manage_page(manage_page $page): string {
        $data = $page->export_for_template($this);
        $this->page->requires->js_call_amd('block_slider/manage', 'init', [$data['blockid']]);
        return $this->render_from_template('block_slider/manage', $data);
    }
}
