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
 * Zadanie odtwarzania bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/blocks/slider/backup/moodle2/restore_slider_stepslib.php');

/**
 * Odtwarzanie slajdów wraz z grafikami.
 *
 * @package     block_slider
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_slider_block_task extends restore_block_task {
    /**
     * Brak własnych ustawień.
     */
    protected function define_my_settings() {
    }

    /**
     * Krok ze strukturą slajdów.
     */
    protected function define_my_steps() {
        $this->add_step(new restore_slider_block_structure_step('slider_structure', 'slider.xml'));
    }

    /**
     * Pliki odtwarza krok struktury (z mapowaniem identyfikatorów slajdów).
     *
     * @return array
     */
    public function get_fileareas() {
        return [];
    }

    /**
     * Konfiguracja nie zawiera zakodowanych linków.
     *
     * @return array
     */
    public function get_configdata_encoded_attributes() {
        return [];
    }

    /**
     * Brak treści do dekodowania.
     *
     * @return array
     */
    public static function define_decode_contents() {
        return [];
    }

    /**
     * Brak reguł dekodowania.
     *
     * @return array
     */
    public static function define_decode_rules() {
        return [];
    }
}
