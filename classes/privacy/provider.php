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

namespace block_slider\privacy;

use block_slider\local\slides;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Prywatność bloku slajdera.
 *
 * Slajdy nie są przypisane do autora. Danymi osobowymi są tylko slajdery na kokpicie
 * użytkownika - należą do właściciela kokpitu, tak jak w bloku HTML.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Opis przechowywanych danych.
     *
     * @param collection $collection kolekcja do uzupełnienia
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(slides::TABLE, [
            'title' => 'privacy:metadata:block_slider_slides:title',
            'subtitle' => 'privacy:metadata:block_slider_slides:subtitle',
            'description' => 'privacy:metadata:block_slider_slides:description',
            'link' => 'privacy:metadata:block_slider_slides:link',
            'buttontext' => 'privacy:metadata:block_slider_slides:buttontext',
            'alttext' => 'privacy:metadata:block_slider_slides:alttext',
            'timemodified' => 'privacy:metadata:block_slider_slides:timemodified',
        ], 'privacy:metadata:block_slider_slides');
        $collection->link_subsystem('block', 'privacy:metadata:block');
        $collection->link_subsystem('core_files', 'privacy:metadata:core_files');

        return $collection;
    }

    /**
     * Konteksty z danymi użytkownika - slajdery na jego kokpicie.
     *
     * @param int $userid użytkownik
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        $sql = "SELECT c.id
                  FROM {block_instances} b
                  JOIN {context} c ON c.instanceid = b.id AND c.contextlevel = :contextblock
                  JOIN {context} bpc ON bpc.id = b.parentcontextid
                 WHERE b.blockname = 'slider'
                   AND bpc.contextlevel = :contextuser
                   AND bpc.instanceid = :userid";
        $contextlist->add_from_sql($sql, [
            'contextblock' => CONTEXT_BLOCK,
            'contextuser' => CONTEXT_USER,
            'userid' => $userid,
        ]);

        return $contextlist;
    }

    /**
     * Użytkownicy z danymi w kontekście bloku.
     *
     * @param userlist $userlist lista do uzupełnienia
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_block) {
            return;
        }

        $sql = "SELECT bpc.instanceid AS userid
                  FROM {block_instances} bi
                  JOIN {context} bpc ON bpc.id = bi.parentcontextid
                 WHERE bi.blockname = 'slider'
                   AND bpc.contextlevel = :contextuser
                   AND bi.id = :blockinstanceid";
        $userlist->add_from_sql('userid', $sql, [
            'contextuser' => CONTEXT_USER,
            'blockinstanceid' => $context->instanceid,
        ]);
    }

    /**
     * Eksport slajdów i grafik.
     *
     * @param approved_contextlist $contextlist zatwierdzone konteksty
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        $user = $contextlist->get_user();

        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof \context_block || !($instance = static::get_instance_from_context($context))) {
                continue;
            }
            $parent = $context->get_parent_context();
            if (!$parent || $parent->contextlevel != CONTEXT_USER || (int) $parent->instanceid !== (int) $user->id) {
                continue;
            }
            $block = block_instance('slider', $instance);
            $data = helper::get_context_data($context, $user);
            $data->title = $block->title;
            $data->slides = [];

            foreach (slides::get_slides($instance->id) as $slide) {
                $subcontext = [get_string('slides', 'block_slider'), $slide->id];
                $data->slides[] = (object) [
                    'title' => $slide->title,
                    'subtitle' => $slide->subtitle,
                    'description' => $slide->description,
                    'link' => $slide->link,
                    'buttontext' => $slide->buttontext,
                    'alttext' => $slide->alttext,
                    'visible' => transform::yesno($slide->visible),
                    'timemodified' => transform::datetime($slide->timemodified),
                ];
                $writer = writer::with_context($context);
                $writer->export_area_files($subcontext, 'block_slider', slides::AREA_IMAGE, $slide->id);
                $writer->export_area_files($subcontext, 'block_slider', slides::AREA_MOBILE, $slide->id);
            }

            writer::with_context($context)->export_data([], $data);
        }
    }

    /**
     * Usunięcie danych wszystkich użytkowników w kontekście.
     *
     * Usuwana jest cała instancja bloku, ale tylko na kokpicie - slajdy kursów i strony głównej
     * nie są danymi osobowymi.
     *
     * @param \context $context kontekst
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if (!$context instanceof \context_block || !($instance = static::get_instance_from_context($context))) {
            return;
        }
        $parent = $context->get_parent_context();
        if ($parent && $parent->contextlevel == CONTEXT_USER) {
            blocks_delete_instance($instance);
        }
    }

    /**
     * Usunięcie danych wybranych użytkowników w jednym kontekście.
     *
     * @param approved_userlist $userlist zatwierdzona lista
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        static::delete_data_for_all_users_in_context($userlist->get_context());
    }

    /**
     * Usunięcie danych użytkownika w wybranych kontekstach.
     *
     * @param approved_contextlist $contextlist zatwierdzone konteksty
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        foreach ($contextlist as $context) {
            static::delete_data_for_all_users_in_context($context);
        }
    }

    /**
     * Instancja bloku slajdera dla kontekstu.
     *
     * @param \context_block $context kontekst bloku
     * @return \stdClass|false
     */
    protected static function get_instance_from_context(\context_block $context) {
        global $DB;

        return $DB->get_record('block_instances', ['id' => $context->instanceid, 'blockname' => 'slider']);
    }
}
