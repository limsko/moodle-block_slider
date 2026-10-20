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

namespace block_slider\output;

use block_slider\local\slides;
use context_block;
use moodle_url;
use renderable;
use renderer_base;
use templatable;

/**
 * Lista slajdów w panelu zarządzania.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manage_page implements renderable, templatable {
    /** @var int Instancja bloku. */
    protected $blockinstanceid;

    /** @var context_block Kontekst bloku. */
    protected $context;

    /** @var moodle_url|null Powrót na stronę z blokiem. */
    protected $returnurl;

    /**
     * Konstruktor.
     *
     * @param int $blockinstanceid instancja bloku
     * @param context_block $context kontekst bloku
     * @param moodle_url|null $returnurl adres strony z blokiem
     */
    public function __construct(int $blockinstanceid, context_block $context, ?moodle_url $returnurl) {
        $this->blockinstanceid = $blockinstanceid;
        $this->context = $context;
        $this->returnurl = $returnurl;
    }

    /**
     * Dane dla szablonu block_slider/manage.
     *
     * @param renderer_base $output renderer
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $images = slides::get_images($this->context);
        $formatoptions = ['context' => $this->context];
        $baseparams = ['blockid' => $this->blockinstanceid];
        if ($this->returnurl) {
            $baseparams['returnurl'] = $this->returnurl->out_as_local_url(false);
        }

        $items = [];
        $position = 0;
        foreach (slides::get_slides($this->blockinstanceid) as $slide) {
            $position++;
            $params = $baseparams + ['id' => $slide->id];
            $actionparams = $params + ['sesskey' => sesskey()];
            $title = format_string((string) $slide->title, true, $formatoptions);
            $image = $images[$slide->id][slides::AREA_IMAGE] ?? null;
            $mobile = $images[$slide->id][slides::AREA_MOBILE] ?? null;
            $link = slides::link_url((string) $slide->link);
            $buttontext = format_string((string) $slide->buttontext, true, $formatoptions);

            $name = $title !== '' ? strip_tags($title) : get_string('slidenumber', 'block_slider', $position);
            $manageurl = new moodle_url('/blocks/slider/manage.php', $actionparams);

            $items[] = [
                'id' => $slide->id,
                'position' => $position,
                'title' => $title,
                'untitled' => $title === '',
                'movetitle' => get_string('movecontent', 'moodle', $name),
                'subtitle' => format_string((string) $slide->subtitle, true, $formatoptions),
                'description' => shorten_text(format_string((string) $slide->description, true, $formatoptions), 140),
                'thumburl' => $image ? slides::file_url($image, 'bigthumb')->out(false) : '',
                'mobilethumburl' => $mobile ? slides::file_url($mobile, 'thumb')->out(false) : '',
                'link' => $link ? $link->out(false) : '',
                'linkshort' => $link ? self::short_url($link->out(false)) : '',
                'newwindow' => $link && !empty($slide->newwindow),
                'buttonlabel' => $link && $buttontext !== '' ? get_string('buttonx', 'block_slider', $buttontext) : '',
                'visible' => !empty($slide->visible),
                'editurl' => (new moodle_url('/blocks/slider/edit.php', $params))->out(false),
                'deleteurl' => (new moodle_url('/blocks/slider/manage.php', $params + ['action' => 'delete']))->out(false),
                'moveupurl' => (new moodle_url($manageurl, ['action' => 'move', 'direction' => 'up']))->out(false),
                'movedownurl' => (new moodle_url($manageurl, ['action' => 'move', 'direction' => 'down']))->out(false),
                'toggleurl' => (new moodle_url($manageurl, ['action' => empty($slide->visible) ? 'show' : 'hide']))->out(false),
            ];
        }

        return [
            'blockid' => $this->blockinstanceid,
            'addurl' => (new moodle_url('/blocks/slider/edit.php', $baseparams))->out(false),
            'returnurl' => $this->returnurl ? $this->returnurl->out(false) : '',
            'slides' => $items,
            'hasslides' => !empty($items),
            'sortable' => count($items) > 1,
            'filtercode' => filter_is_enabled('slider') ? '[SLIDER-' . $this->blockinstanceid . ']' : '',
        ];
    }

    /**
     * Skrócony zapis adresu: host i ścieżka, bez protokołu i www.
     *
     * @param string $url pełny adres
     * @param int $length maksymalna długość
     * @return string
     */
    public static function short_url(string $url, int $length = 48): string {
        $parts = parse_url($url);
        if (empty($parts['host'])) {
            return shorten_text($url, $length, true);
        }
        $short = preg_replace('/^www\./', '', $parts['host']) . ($parts['path'] ?? '');
        if (!empty($parts['query']) || !empty($parts['fragment'])) {
            $short .= '?...';
        }
        return shorten_text(rtrim($short, '/'), $length, true);
    }
}
