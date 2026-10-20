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
 * Aktualizacja bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Aktualizuje blok do bieżącej wersji.
 *
 * @param int $oldversion wersja zainstalowana
 * @return bool
 */
function xmldb_block_slider_upgrade($oldversion) {
    global $DB;

    require_once(__DIR__ . '/upgradelib.php');
    $dbman = $DB->get_manager();

    if ($oldversion < 2026102000) {
        // Nowa tabela zgodna z nazewnictwem wtyczek, dane i pliki przenoszone ze starej.
        $table = new xmldb_table('block_slider_slides');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('blockinstanceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('title', XMLDB_TYPE_CHAR, '255');
        $table->add_field('subtitle', XMLDB_TYPE_CHAR, '255');
        $table->add_field('description', XMLDB_TYPE_TEXT);
        $table->add_field('link', XMLDB_TYPE_CHAR, '1333');
        $table->add_field('newwindow', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('buttontext', XMLDB_TYPE_CHAR, '255');
        $table->add_field('alttext', XMLDB_TYPE_CHAR, '255');
        $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('visible', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('blockinstanceid', XMLDB_KEY_FOREIGN, ['blockinstanceid'], 'block_instances', ['id']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        block_slider_migrate_legacy_slides();
        block_slider_migrate_legacy_config();

        $oldtable = new xmldb_table('slider_slides');
        if ($dbman->table_exists($oldtable)) {
            $dbman->drop_table($oldtable);
        }

        upgrade_block_savepoint(true, 2026102000, 'slider');
    }

    return true;
}
