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
 * Funkcje bloku slajdera wywoływane przez rdzeń.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_slider\local\slides;

/**
 * Udostępnia grafiki slajdów.
 *
 * @param stdClass $course kurs
 * @param stdClass $birecordorcm rekord instancji bloku
 * @param context $context kontekst bloku
 * @param string $filearea obszar plików
 * @param array $args pozostałe elementy ścieżki
 * @param bool $forcedownload wymuszenie pobrania
 * @param array $options dodatkowe opcje
 * @return bool false, gdy pliku nie ma
 */
function block_slider_pluginfile($course, $birecordorcm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_BLOCK || !in_array($filearea, [slides::AREA_IMAGE, slides::AREA_MOBILE], true)) {
        send_file_not_found();
    }

    if ($context->get_course_context(false)) {
        require_course_login($course);
    } else if ($CFG->forcelogin) {
        require_login();
    } else {
        $parentcontext = $context->get_parent_context();
        if ($parentcontext->contextlevel === CONTEXT_COURSECAT) {
            if (!core_course_category::get($parentcontext->instanceid, IGNORE_MISSING)) {
                send_file_not_found();
            }
        } else if ($parentcontext->contextlevel === CONTEXT_USER) {
            require_once($CFG->dirroot . '/user/lib.php');
            $user = core_user::get_user($parentcontext->instanceid, '*', MUST_EXIST);
            if (!user_can_view_profile($user, null, $parentcontext)) {
                send_file_not_found();
            }
        }
    }

    $itemid = (int) array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    // Ukryte slajdy widzą tylko osoby zarządzające.
    $slide = $DB->get_record(slides::TABLE, ['id' => $itemid, 'blockinstanceid' => $context->instanceid]);
    if (!$slide || (empty($slide->visible) && !slides::can_manage($context))) {
        send_file_not_found();
    }

    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'block_slider', $filearea, $itemid, $filepath, $filename);
    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    $parentcontext = context::instance_by_id($birecordorcm->parentcontextid, IGNORE_MISSING);
    if (!$parentcontext || $parentcontext->contextlevel == CONTEXT_USER) {
        // Strony osobiste: pobieranie zamiast wyświetlania poza znacznikiem img.
        $forcedownload = true;
    }

    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
