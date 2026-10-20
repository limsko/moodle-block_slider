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

use block_slider\local\manage_helper;
use block_slider\local\slides;
use context_block;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * Zapis kolejności slajdów po przeciągnięciu na liście.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reorder_slides extends external_api {
    /**
     * Parametry wejściowe.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'blockid' => new external_value(PARAM_INT, 'Block instance id'),
            'slideids' => new external_multiple_structure(
                new external_value(PARAM_INT, 'Slide id'),
                'Slide ids in the new order'
            ),
        ]);
    }

    /**
     * Zapisuje kolejność.
     *
     * @param int $blockid instancja bloku
     * @param int[] $slideids identyfikatory slajdów w nowej kolejności
     * @return array
     */
    public static function execute(int $blockid, array $slideids): array {
        $params = self::validate_parameters(self::execute_parameters(), ['blockid' => $blockid, 'slideids' => $slideids]);

        $instance = manage_helper::get_instance($params['blockid']);
        $context = context_block::instance($instance->id);
        self::validate_context($context);
        slides::require_manage($context);

        return ['slideids' => slides::reorder($instance->id, $params['slideids'])];
    }

    /**
     * Wynik: kolejność zapisana w bazie.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'slideids' => new external_multiple_structure(new external_value(PARAM_INT, 'Slide id'), 'Saved order'),
        ]);
    }
}
