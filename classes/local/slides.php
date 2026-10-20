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

use context;
use context_block;
use moodle_url;
use stdClass;
use stored_file;

/**
 * Operacje na slajdach: odczyt, zapis, kolejność, widoczność, pliki i uprawnienia.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slides {
    /** @var string Tabela slajdów. */
    public const TABLE = 'block_slider_slides';

    /** @var string Obszar plików z główną grafiką slajdu. */
    public const AREA_IMAGE = 'slide';

    /** @var string Obszar plików z grafiką na wąskie ekrany. */
    public const AREA_MOBILE = 'slidemobile';

    /** @var string[] Pola slajdu edytowane w formularzu. */
    public const FIELDS = ['title', 'subtitle', 'description', 'link', 'newwindow', 'buttontext', 'alttext', 'visible'];

    /**
     * Slajdy instancji bloku w kolejności wyświetlania.
     *
     * @param int $blockinstanceid instancja bloku
     * @param bool $visibleonly tylko widoczne
     * @return stdClass[] indeksowane identyfikatorem slajdu
     */
    public static function get_slides(int $blockinstanceid, bool $visibleonly = false): array {
        global $DB;

        $params = ['blockinstanceid' => $blockinstanceid];
        if ($visibleonly) {
            $params['visible'] = 1;
        }
        return $DB->get_records(self::TABLE, $params, 'sortorder ASC, id ASC');
    }

    /**
     * Pojedynczy slajd, opcjonalnie z kontrolą przynależności do instancji bloku.
     *
     * @param int $id identyfikator slajdu
     * @param int|null $blockinstanceid instancja bloku, do której slajd musi należeć
     * @return stdClass
     */
    public static function get_slide(int $id, ?int $blockinstanceid = null): stdClass {
        global $DB;

        $params = ['id' => $id];
        if ($blockinstanceid !== null) {
            $params['blockinstanceid'] = $blockinstanceid;
        }
        return $DB->get_record(self::TABLE, $params, '*', MUST_EXIST);
    }

    /**
     * Dodaje slajd na końcu listy.
     *
     * @param int $blockinstanceid instancja bloku
     * @param stdClass $data pola slajdu
     * @return stdClass zapisany rekord
     */
    public static function create(int $blockinstanceid, stdClass $data): stdClass {
        global $DB;

        $record = self::clean_record($data);
        $record->blockinstanceid = $blockinstanceid;
        $record->visible = $record->visible ?? 1;
        $record->sortorder = count(self::normalise_order($blockinstanceid));
        $record->timecreated = $record->timemodified = time();
        $record->id = $DB->insert_record(self::TABLE, $record);

        return self::get_slide($record->id);
    }

    /**
     * Aktualizuje pola slajdu.
     *
     * @param stdClass $slide istniejący slajd
     * @param stdClass $data nowe wartości pól
     * @return stdClass zapisany rekord
     */
    public static function update(stdClass $slide, stdClass $data): stdClass {
        global $DB;

        $record = self::clean_record($data);
        $record->id = $slide->id;
        $record->timemodified = time();
        $DB->update_record(self::TABLE, $record);

        return self::get_slide($slide->id);
    }

    /**
     * Usuwa slajd wraz z grafikami.
     *
     * @param stdClass $slide slajd
     */
    public static function delete(stdClass $slide): void {
        global $DB;

        $context = context_block::instance($slide->blockinstanceid, IGNORE_MISSING);
        if ($context) {
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'block_slider', self::AREA_IMAGE, $slide->id);
            $fs->delete_area_files($context->id, 'block_slider', self::AREA_MOBILE, $slide->id);
        }
        $DB->delete_records(self::TABLE, ['id' => $slide->id]);
        self::normalise_order($slide->blockinstanceid);
    }

    /**
     * Usuwa wszystkie slajdy instancji bloku.
     *
     * @param int $blockinstanceid instancja bloku
     */
    public static function delete_all(int $blockinstanceid): void {
        global $DB;

        $context = context_block::instance($blockinstanceid, IGNORE_MISSING);
        if ($context) {
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'block_slider', self::AREA_IMAGE);
            $fs->delete_area_files($context->id, 'block_slider', self::AREA_MOBILE);
        }
        $DB->delete_records(self::TABLE, ['blockinstanceid' => $blockinstanceid]);
    }

    /**
     * Pokazuje albo ukrywa slajd.
     *
     * @param stdClass $slide slajd
     * @param bool $visible nowy stan
     */
    public static function set_visible(stdClass $slide, bool $visible): void {
        global $DB;

        $DB->update_record(self::TABLE, (object) ['id' => $slide->id, 'visible' => (int) $visible, 'timemodified' => time()]);
    }

    /**
     * Przesuwa slajd o jedną pozycję.
     *
     * @param stdClass $slide slajd
     * @param string $direction up albo down
     * @return bool czy kolejność się zmieniła
     */
    public static function move(stdClass $slide, string $direction): bool {
        $ids = self::normalise_order($slide->blockinstanceid);
        $index = array_search((int) $slide->id, $ids, true);
        $target = ($direction === 'up') ? $index - 1 : $index + 1;
        if ($index === false || !isset($ids[$target])) {
            return false;
        }
        [$ids[$index], $ids[$target]] = [$ids[$target], $ids[$index]];
        self::reorder($slide->blockinstanceid, $ids);
        return true;
    }

    /**
     * Ustawia kolejność slajdów według listy identyfikatorów.
     *
     * Slajdy spoza listy trafiają na koniec, identyfikatory obcych slajdów są pomijane.
     *
     * @param int $blockinstanceid instancja bloku
     * @param int[] $slideids identyfikatory w nowej kolejności
     * @return int[] identyfikatory w zapisanej kolejności
     */
    public static function reorder(int $blockinstanceid, array $slideids): array {
        global $DB;

        $current = self::normalise_order($blockinstanceid);
        $ordered = array_values(array_intersect(array_unique(array_map('intval', $slideids)), $current));
        $ordered = array_merge($ordered, array_values(array_diff($current, $ordered)));

        $transaction = $DB->start_delegated_transaction();
        foreach ($ordered as $position => $id) {
            $DB->set_field(self::TABLE, 'sortorder', $position, ['id' => $id]);
        }
        $transaction->allow_commit();

        return $ordered;
    }

    /**
     * Porządkuje kolejność do 0..n-1 bez powtórzeń.
     *
     * @param int $blockinstanceid instancja bloku
     * @return int[] identyfikatory slajdów w kolejności
     */
    public static function normalise_order(int $blockinstanceid): array {
        global $DB;

        $slides = $DB->get_records(self::TABLE, ['blockinstanceid' => $blockinstanceid], 'sortorder ASC, id ASC', 'id, sortorder');
        $ids = [];
        foreach (array_values($slides) as $position => $slide) {
            if ((int) $slide->sortorder !== $position) {
                $DB->set_field(self::TABLE, 'sortorder', $position, ['id' => $slide->id]);
            }
            $ids[] = (int) $slide->id;
        }
        return $ids;
    }

    /**
     * Kopiuje slajdy z grafikami do innej instancji bloku.
     *
     * @param int $fromid źródłowa instancja bloku
     * @param int $toid docelowa instancja bloku
     * @return int liczba skopiowanych slajdów
     */
    public static function copy_all(int $fromid, int $toid): int {
        global $DB;

        $fromcontext = context_block::instance($fromid);
        $tocontext = context_block::instance($toid);
        $fs = get_file_storage();
        $count = 0;

        foreach (self::get_slides($fromid) as $slide) {
            $copy = clone($slide);
            unset($copy->id);
            $copy->blockinstanceid = $toid;
            $copy->timecreated = $copy->timemodified = time();
            $newid = $DB->insert_record(self::TABLE, $copy);

            foreach ([self::AREA_IMAGE, self::AREA_MOBILE] as $area) {
                foreach ($fs->get_area_files($fromcontext->id, 'block_slider', $area, $slide->id, 'id', false) as $file) {
                    $fs->create_file_from_storedfile(['contextid' => $tocontext->id, 'itemid' => $newid], $file);
                }
            }
            $count++;
        }
        return $count;
    }

    /**
     * Grafiki wszystkich slajdów bloku jednym zapytaniem.
     *
     * @param context $context kontekst bloku
     * @return array [id slajdu => [obszar => stored_file]]
     */
    public static function get_images(context $context): array {
        $fs = get_file_storage();
        $images = [];
        foreach ([self::AREA_IMAGE, self::AREA_MOBILE] as $area) {
            $files = $fs->get_area_files($context->id, 'block_slider', $area, false, 'itemid, filepath, filename', false);
            foreach ($files as $file) {
                $images[$file->get_itemid()][$area] = $images[$file->get_itemid()][$area] ?? $file;
            }
        }
        return $images;
    }

    /**
     * Adres pliku grafiki.
     *
     * @param stored_file $file plik
     * @param string|null $preview tryb podglądu rdzenia (thumb, bigthumb)
     * @return moodle_url
     */
    public static function file_url(stored_file $file, ?string $preview = null): moodle_url {
        $url = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename()
        );
        if ($preview) {
            $url->param('preview', $preview);
        }
        return $url;
    }

    /**
     * Opcje pól plików z grafikami.
     *
     * @return array
     */
    public static function file_options(): array {
        return [
            'subdirs' => 0,
            'maxfiles' => 1,
            'maxbytes' => 0,
            'accepted_types' => ['web_image'],
        ];
    }

    /**
     * Czy link jest poprawny: pełny adres http(s) albo ścieżka zaczynająca się od "/".
     *
     * @param string $link link
     * @return bool
     */
    public static function is_valid_link(string $link): bool {
        if ($link === '') {
            return true;
        }
        if (strpos($link, '/') === 0 && strpos($link, '//') !== 0) {
            return clean_param('http://localhost' . $link, PARAM_URL) === 'http://localhost' . $link;
        }
        return preg_match('~^https?://~i', $link) && clean_param($link, PARAM_URL) === $link;
    }

    /**
     * Pełny adres linku slajdu (ścieżki względne są liczone od wwwroot).
     *
     * @param string $link zapisany link
     * @return moodle_url|null
     */
    public static function link_url(string $link): ?moodle_url {
        $link = trim($link);
        if ($link === '') {
            return null;
        }
        if (!preg_match('~^https?://~i', $link)) {
            $link = '/' . ltrim($link, '/');
        }
        return new moodle_url($link);
    }

    /**
     * Czy bieżący użytkownik może zarządzać slajdami bloku.
     *
     * Na własnym kokpicie wystarcza prawo do zarządzania blokami kokpitu.
     *
     * @param context_block $context kontekst bloku
     * @return bool
     */
    public static function can_manage(context_block $context): bool {
        global $USER;

        if (has_capability('block/slider:manage', $context)) {
            return true;
        }
        $parent = $context->get_parent_context();
        return $parent && $parent->contextlevel == CONTEXT_USER && (int) $parent->instanceid === (int) $USER->id
            && has_capability('moodle/my:manageblocks', $parent);
    }

    /**
     * Wymaga prawa do zarządzania slajdami bloku.
     *
     * @param context_block $context kontekst bloku
     */
    public static function require_manage(context_block $context): void {
        if (!self::can_manage($context)) {
            throw new \required_capability_exception($context, 'block/slider:manage', 'nopermissions', '');
        }
    }

    /**
     * Rekord z dozwolonymi polami i oczyszczonymi wartościami.
     *
     * @param stdClass $data dane wejściowe
     * @return stdClass
     */
    private static function clean_record(stdClass $data): stdClass {
        $record = new stdClass();
        foreach (self::FIELDS as $field) {
            if (!property_exists($data, $field)) {
                continue;
            }
            if (in_array($field, ['newwindow', 'visible'], true)) {
                $record->$field = (int) !empty($data->$field);
            } else {
                $record->$field = trim((string) $data->$field);
            }
        }
        return $record;
    }
}
