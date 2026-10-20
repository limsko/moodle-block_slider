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
 * Kroki Behat bloku slajdera.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

/**
 * Selektory i strony bloku slajdera.
 *
 * @package     block_slider
 * @category    test
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_slider extends behat_base {
    /**
     * Selektory: "Slide" na liście zarządzania (po tytule), "Slider slide" w karuzeli (po tytule).
     *
     * @return behat_component_named_selector[]
     */
    public static function get_partial_named_selectors(): array {
        return [
            new behat_component_named_selector('Slide', [
                "//li[contains(concat(' ', normalize-space(@class), ' '), ' block-slider-manage-item ')]" .
                    "[.//*[@data-region='title'][contains(normalize-space(.), %locator%)]]",
            ]),
            new behat_component_named_selector('Slider slide', [
                "//*[contains(concat(' ', normalize-space(@class), ' '), ' block-slider-slide ')]" .
                    "[.//*[contains(concat(' ', normalize-space(@class), ' '), ' block-slider-caption-title ')]" .
                    "[contains(normalize-space(.), %locator%)]]",
            ]),
        ];
    }

    /**
     * Strona zarządzania slajdami bloku o podanym tytule.
     *
     * Przykład: When I am on the "Banners" "block_slider > Manage slides" page
     *
     * @param string $type typ strony
     * @param string $identifier tytuł bloku
     * @return moodle_url
     */
    protected function resolve_page_instance_url(string $type, string $identifier): moodle_url {
        global $DB;

        if (strtolower($type) !== 'manage slides') {
            throw new Exception('Unrecognised block_slider page type "' . $type . '"');
        }
        foreach ($DB->get_records('block_instances', ['blockname' => 'slider'], 'id ASC', 'id, configdata') as $instance) {
            $config = empty($instance->configdata) ? null : unserialize_object(base64_decode($instance->configdata));
            if (is_object($config) && isset($config->title) && $config->title === $identifier) {
                return new moodle_url('/blocks/slider/manage.php', ['blockid' => $instance->id]);
            }
        }
        throw new Exception('Slider block with title "' . $identifier . '" does not exist');
    }
}
