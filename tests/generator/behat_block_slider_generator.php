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
 * Generator Behat bloku slajdera.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Encje Behat: slajdy wskazywane tytułem bloku.
 *
 * Przykład:
 * | block    | title | link        |
 * | Banners  | First | /index.php  |
 *
 * @package     block_slider
 * @category    test
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_block_slider_generator extends behat_generator_base {
    /**
     * Encje tworzone przez generator.
     *
     * @return array
     */
    protected function get_creatable_entities(): array {
        return [
            'slides' => [
                'singular' => 'slide',
                'datagenerator' => 'slide',
                'required' => ['block'],
                'switchids' => ['block' => 'blockinstanceid'],
            ],
        ];
    }

    /**
     * Instancja bloku slajdera o podanym tytule.
     *
     * @param string $title tytuł z ustawień bloku
     * @return int
     */
    protected function get_block_id(string $title): int {
        global $DB;

        $instances = $DB->get_records('block_instances', ['blockname' => 'slider'], 'id ASC', 'id, configdata');
        foreach ($instances as $instance) {
            $config = empty($instance->configdata) ? null : unserialize_object(base64_decode($instance->configdata));
            if (is_object($config) && isset($config->title) && $config->title === $title) {
                return (int) $instance->id;
            }
        }
        throw new Exception('Slider block with title "' . $title . '" does not exist');
    }
}
