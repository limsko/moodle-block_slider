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
 * Funkcje pomocnicze aktualizacji - przeniesienie danych ze starszych wersji bloku.
 *
 * Kod celowo nie korzysta z klas wtyczki, bo te mogą się zmieniać w kolejnych wersjach.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Przenosi slajdy z tabeli slider_slides (wersje do 0.6) do block_slider_slides.
 *
 * Pliki trafiają do nowych obszarów (slide, slidemobile) z identyfikatorem nowego rekordu.
 * Najstarsze wersje trzymały grafiki w obszarze "content" bez tabeli - te też są przenoszone.
 *
 * @return int liczba przeniesionych slajdów
 */
function block_slider_migrate_legacy_slides(): int {
    global $DB;

    $dbman = $DB->get_manager();
    $fs = get_file_storage();
    $count = 0;
    $now = time();

    if ($dbman->table_exists('slider_slides')) {
        $orders = [];
        $rs = $DB->get_recordset('slider_slides', null, 'sliderid ASC, slide_order ASC, id ASC');
        foreach ($rs as $old) {
            $context = context_block::instance($old->sliderid, IGNORE_MISSING);
            if (!$context || !$DB->record_exists('block_instances', ['id' => $old->sliderid, 'blockname' => 'slider'])) {
                continue;
            }
            $orders[$old->sliderid] = isset($orders[$old->sliderid]) ? $orders[$old->sliderid] + 1 : 0;

            $record = (object) [
                'blockinstanceid' => $old->sliderid,
                'title' => block_slider_legacy_text($old->slide_title ?? null, 255),
                'subtitle' => block_slider_legacy_text($old->slide_title2 ?? null, 255),
                'description' => trim((string) ($old->slide_desc ?? '')),
                'link' => block_slider_legacy_link($old->slide_link ?? null),
                'newwindow' => (($old->slide_target ?? '') === '_blank') ? 1 : 0,
                'buttontext' => block_slider_legacy_text($old->slide_button ?? null, 255),
                'alttext' => block_slider_legacy_text($old->slide_alt ?? null, 255),
                'sortorder' => $orders[$old->sliderid],
                'visible' => 1,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $newid = $DB->insert_record('block_slider_slides', $record);

            foreach (['slider_slides' => 'slide', 'slider_slides_mobile' => 'slidemobile'] as $oldarea => $newarea) {
                $files = $fs->get_area_files($context->id, 'block_slider', $oldarea, $old->id, 'id ASC', false);
                foreach (array_slice($files, 0, 1) as $file) {
                    $fs->create_file_from_storedfile(['filearea' => $newarea, 'itemid' => $newid, 'filepath' => '/'], $file);
                }
                $fs->delete_area_files($context->id, 'block_slider', $oldarea, $old->id);
            }
            $count++;
        }
        $rs->close();
    }

    // Grafiki z obszaru "content" (wersje 0.1) oraz jego pozostałości po wcześniejszej migracji.
    $instances = $DB->get_records('block_instances', ['blockname' => 'slider'], 'id ASC', 'id');
    foreach ($instances as $instance) {
        $context = context_block::instance($instance->id, IGNORE_MISSING);
        if (!$context) {
            continue;
        }
        $hasslides = $DB->record_exists('block_slider_slides', ['blockinstanceid' => $instance->id]);
        if (!$hasslides) {
            $files = $fs->get_area_files($context->id, 'block_slider', 'content', false, 'filename ASC', false);
            $order = 0;
            foreach ($files as $file) {
                if (!$file->is_valid_image()) {
                    continue;
                }
                $newid = $DB->insert_record('block_slider_slides', (object) [
                    'blockinstanceid' => $instance->id,
                    'sortorder' => $order++,
                    'visible' => 1,
                    'newwindow' => 0,
                    'timecreated' => $now,
                    'timemodified' => $now,
                ]);
                $fs->create_file_from_storedfile(['filearea' => 'slide', 'itemid' => $newid, 'filepath' => '/'], $file);
                $count++;
            }
        }
        $fs->delete_area_files($context->id, 'block_slider', 'content');
    }

    return $count;
}

/**
 * Przycina stary tekst do długości pola.
 *
 * @param string|null $value wartość ze starej tabeli
 * @param int $length maksymalna długość
 * @return string
 */
function block_slider_legacy_text(?string $value, int $length): string {
    return core_text::substr(trim((string) $value), 0, $length);
}

/**
 * Stary link: ścieżki względne dostają początkowy "/", jak wymaga formularz slajdu.
 *
 * @param string|null $link link ze starej tabeli
 * @return string
 */
function block_slider_legacy_link(?string $link): string {
    $link = block_slider_legacy_text($link, 1333);
    if ($link !== '' && !preg_match('~^[a-z][a-z0-9+.-]*://~i', $link) && strpos($link, '/') !== 0) {
        $link = '/' . $link;
    }
    return $link;
}

/**
 * Zamienia ustawienia instancji ze starszych wersji (BX Slider, SlidesJS, Glider) na bieżące.
 *
 * @return int liczba zaktualizowanych instancji
 */
function block_slider_migrate_legacy_config(): int {
    global $DB;

    $count = 0;
    $rs = $DB->get_recordset('block_instances', ['blockname' => 'slider'], 'id ASC', 'id, configdata');
    foreach ($rs as $instance) {
        $old = empty($instance->configdata) ? null : unserialize_object(base64_decode($instance->configdata));
        if (!is_object($old) || isset($old->captionstyle)) {
            // Brak ustawień (blok dostanie domyślne) albo instancja ma już nowy format.
            continue;
        }
        $new = block_slider_convert_legacy_config($old);
        $DB->set_field('block_instances', 'configdata', base64_encode(serialize($new)), ['id' => $instance->id]);
        $count++;
    }
    $rs->close();

    return $count;
}

/**
 * Mapuje stary obiekt ustawień na nowy.
 *
 * @param stdClass $old stare ustawienia
 * @return stdClass
 */
function block_slider_convert_legacy_config(stdClass $old): stdClass {
    $first = function (array $keys, $default) use ($old) {
        foreach ($keys as $key) {
            if (isset($old->$key) && $old->$key !== '') {
                return $old->$key;
            }
        }
        return $default;
    };

    $isbx = isset($old->slider_js) && trim($old->slider_js) === 'bxslider';
    if ($isbx) {
        $effect = (($old->bx_effect ?? 'fade') === 'fade') ? 'fade' : 'slide';
        $speed = (int) ($old->bx_speed ?? 500);
    } else if (isset($old->slider_js)) {
        $effect = (($old->effect ?? 'fade') === 'fade') ? 'fade' : 'slide';
        $speed = 500;
    } else {
        $effect = 'slide';
        $speed = (int) round(1000 * (float) ($old->duration ?? 0.5));
    }

    $new = new stdClass();
    $new->title = trim((string) ($old->text ?? ''));
    $new->device = (int) ($old->device ?? 0);
    $new->frameless = 0;
    $new->effect = $effect;
    $new->slidestoshow = max(1, min(6, (int) ($old->slidestoshow ?? 1)));
    $new->imagefit = 'cover';
    $new->maxwidth = 0;
    $new->gap = 16;
    $new->rounded = 0;
    [$new->aspectratio, $new->aspectwidth, $new->aspectheight] =
        block_slider_legacy_ratio((int) ($old->width ?? 940), (int) ($old->height ?? 528));
    $new->mobileaspectratio = '';

    $new->autoplay = (int) (bool) $first(['autoplay', 'bx_auto'], 1);
    $new->interval = max(1000, (int) $first(['interval'], 5000));
    $new->speed = block_slider_legacy_speed($speed);
    $new->loop = (int) (bool) $first(['rewind'], 1);
    $new->pauseonhover = (int) (bool) $first(['pauseonhover'], 1);
    $new->swipe = (int) (bool) $first(['draggable'], 1);

    // Starsze wersje miały strzałki na grafice - zachowujemy ten wygląd.
    $new->controlsposition = 'overlay';
    $new->arrows = (int) (bool) $first(['arrows', 'navigation', 'bx_controls'], 1);
    $new->dots = (int) (bool) $first(['dots', 'pagination', 'bx_pager'], 1);
    $new->pausebutton = (int) (bool) $first(['pausebutton'], 1);
    $accent = trim((string) ($old->accentcolor ?? ''));
    $new->customaccent = preg_match('/^#[0-9a-fA-F]{6}$/', $accent) ? 1 : 0;
    $new->accentcolor = $new->customaccent ? $accent : '';

    $captions = isset($old->showcaptions) ? !empty($old->showcaptions)
        : (!empty($old->bx_captions) || !empty($old->bx_displaydesc));
    $new->captions = (int) $captions;
    $new->captionstyle = 'gradient';
    $new->captionposition = 'bottom';
    $new->captionalign = 'start';
    $new->captionwidth = 75;
    $new->captionsize = 'medium';
    $new->captionbg = 'dark';
    $new->captionopacity = 70;
    $new->captionhover = (int) (bool) $first(['captionhover', 'bx_hideonhover'], 0);

    return $new;
}

/**
 * Dobiera proporcje obrazu do starych wymiarów (szerokość i wysokość w px).
 *
 * @param int $width szerokość
 * @param int $height wysokość
 * @return array [proporcje, szerokość własna, wysokość własna]
 */
function block_slider_legacy_ratio(int $width, int $height): array {
    if ($width <= 0 || $height <= 0) {
        return ['16:9', 16, 9];
    }
    $presets = ['21:9' => 21 / 9, '16:9' => 16 / 9, '2:1' => 2, '3:1' => 3, '4:3' => 4 / 3, '1:1' => 1];
    $ratio = $width / $height;
    foreach ($presets as $name => $value) {
        if (abs($ratio - $value) / $value <= 0.02) {
            [$w, $h] = explode(':', $name);
            return [$name, (int) $w, (int) $h];
        }
    }
    return ['custom', $width, $height];
}

/**
 * Dopasowuje czas przejścia do najbliższej opcji formularza.
 *
 * @param int $speed czas w ms
 * @return int
 */
function block_slider_legacy_speed(int $speed): int {
    $options = [0, 250, 500, 750, 1000, 1500];
    $best = 500;
    foreach ($options as $option) {
        if (abs($option - $speed) < abs($best - $speed)) {
            $best = $option;
        }
    }
    return $best;
}
