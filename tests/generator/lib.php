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
 * Generator danych testowych bloku slajdera.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_slider\local\settings;
use block_slider\local\slides;

/**
 * Generator instancji bloku i slajdów.
 *
 * @package     block_slider
 * @category    test
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_slider_generator extends testing_block_generator {
    /**
     * Ustawienia podane w rekordzie (np. title w tabeli Behat) zapisuje jako configdata.
     *
     * @param stdClass $record rekord instancji
     * @param array $options opcje
     */
    protected function preprocess_record(stdClass $record, array $options) {
        if (isset($record->configdata)) {
            return;
        }
        $config = new stdClass();
        foreach (array_keys(settings::DEFAULTS) as $key) {
            if (isset($record->$key)) {
                $config->$key = $record->$key;
                unset($record->$key);
            } else if (isset($options[$key])) {
                $config->$key = $options[$key];
            }
        }
        if ((array) $config) {
            $record->configdata = base64_encode(serialize($config));
        }
    }

    /**
     * Tworzy slajd z grafiką (domyślnie tests/fixtures/slide1.png).
     *
     * @param array|stdClass $record pola slajdu, wymagane blockinstanceid; opcjonalnie image i mobileimage (ścieżki)
     * @return stdClass
     */
    public function create_slide($record): stdClass {
        $record = (object) (array) $record;
        if (empty($record->blockinstanceid)) {
            throw new coding_exception('blockinstanceid is required');
        }
        $image = $record->image ?? __DIR__ . '/../fixtures/slide1.png';
        $mobile = $record->mobileimage ?? null;
        unset($record->image, $record->mobileimage);

        $slide = slides::create((int) $record->blockinstanceid, $record);
        $context = context_block::instance($slide->blockinstanceid);
        $this->store_image($context, slides::AREA_IMAGE, $slide->id, $image);
        if ($mobile) {
            $this->store_image($context, slides::AREA_MOBILE, $slide->id, $mobile);
        }
        return $slide;
    }

    /**
     * Zapisuje grafikę w obszarze plików slajdu.
     *
     * @param context_block $context kontekst bloku
     * @param string $area obszar plików
     * @param int $itemid identyfikator slajdu
     * @param string $path ścieżka względem katalogu głównego Moodle albo bezwzględna
     */
    protected function store_image(context_block $context, string $area, int $itemid, string $path): void {
        global $CFG;

        $fullpath = is_readable($path) ? $path : $CFG->dirroot . '/' . ltrim($path, '/');
        get_file_storage()->create_file_from_pathname([
            'contextid' => $context->id,
            'component' => 'block_slider',
            'filearea' => $area,
            'itemid' => $itemid,
            'filepath' => '/',
            'filename' => basename($fullpath),
        ], $fullpath);
    }
}
