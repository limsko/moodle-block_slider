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

namespace block_slider\local;

use context_block;
use moodle_url;

/**
 * Wspólna obsługa stron zarządzania slajdami.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_helper {
    /**
     * Instancja bloku slajdera.
     *
     * @param int $blockid identyfikator instancji
     * @return \stdClass
     */
    public static function get_instance(int $blockid): \stdClass {
        global $DB;

        return $DB->get_record('block_instances', ['id' => $blockid, 'blockname' => 'slider'], '*', MUST_EXIST);
    }

    /**
     * Kurs, w którym leży blok (do require_login), albo null poza kursami.
     *
     * @param \stdClass $instance instancja bloku
     * @return \stdClass|null
     */
    public static function get_course(\stdClass $instance): ?\stdClass {
        $coursecontext = context_block::instance($instance->id)->get_course_context(false);
        if ($coursecontext && (int) $coursecontext->instanceid !== (int) SITEID) {
            return get_course($coursecontext->instanceid);
        }
        return null;
    }

    /**
     * Uprawnienia i ustawienia strony zarządzania (po require_login).
     *
     * @param \stdClass $instance instancja bloku
     * @param moodle_url $url adres bieżącej strony
     * @param moodle_url|null $returnurl adres strony z blokiem
     * @return context_block
     */
    public static function setup_page(\stdClass $instance, moodle_url $url, ?moodle_url $returnurl): context_block {
        global $PAGE;

        $context = context_block::instance($instance->id);
        slides::require_manage($context);

        $title = get_string('manageslides', 'block_slider');
        $PAGE->set_context($context);
        $PAGE->set_url($url);
        $PAGE->set_pagelayout('admin');
        $PAGE->set_title($title);
        if ($PAGE->course->id != SITEID) {
            $PAGE->set_heading(format_string($PAGE->course->fullname, true, ['context' => $PAGE->context]));
        }

        $config = empty($instance->configdata) ? null : unserialize_object(base64_decode($instance->configdata));
        $settings = settings::from_config(is_object($config) ? $config : null);
        $blocktitle = $settings['title'] !== '' ? format_string($settings['title'], true, ['context' => $context])
            : get_string('pluginname', 'block_slider');
        $PAGE->navbar->add($blocktitle, $returnurl);
        $PAGE->navbar->add($title, self::manage_url($instance->id, $returnurl));

        return $context;
    }

    /**
     * Adres listy slajdów.
     *
     * @param int $blockid instancja bloku
     * @param moodle_url|null $returnurl adres strony z blokiem
     * @param array $params dodatkowe parametry
     * @return moodle_url
     */
    public static function manage_url(int $blockid, ?moodle_url $returnurl, array $params = []): moodle_url {
        $params['blockid'] = $blockid;
        if ($returnurl) {
            $params['returnurl'] = $returnurl->out_as_local_url(false);
        }
        return new moodle_url('/blocks/slider/manage.php', $params);
    }

    /**
     * Adres powrotu z parametru (tylko lokalne adresy).
     *
     * @param string $returnurl wartość parametru returnurl
     * @return moodle_url|null
     */
    public static function return_url(string $returnurl): ?moodle_url {
        return $returnurl !== '' ? new moodle_url($returnurl) : null;
    }
}
