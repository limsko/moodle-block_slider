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
 * Blok slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_slider\local\settings;
use block_slider\local\slides;

/**
 * Blok wyświetlający karuzelę slajdów z grafikami, napisami i linkami.
 *
 * @package     block_slider
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_slider extends block_base {
    /**
     * Inicjalizacja bloku.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_slider');
    }

    /**
     * Tytuł z ustawień instancji.
     */
    public function specialization() {
        $settings = settings::from_config($this->config);
        if ($settings['title'] !== '') {
            $this->title = format_string($settings['title'], true, ['context' => $this->context]);
        }
    }

    /**
     * Treść bloku.
     *
     * @return stdClass
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';
        if (empty($this->instance)) {
            return $this->content;
        }

        $canmanage = slides::can_manage($this->context);
        $renderer = $this->page->get_renderer('block_slider');
        $slides = slides::get_slides($this->instance->id, true);
        $slider = new \block_slider\output\slider($this->context, $slides, settings::from_config($this->config));
        $this->content->text = $renderer->render($slider);

        if ($canmanage && $this->page->user_is_editing()) {
            if ($this->content->text === '') {
                $this->content->text = html_writer::div(get_string('noslides', 'block_slider'), 'block-slider-empty');
            }
            $url = new moodle_url('/blocks/slider/manage.php', ['blockid' => $this->instance->id]);
            if ($this->page->has_set_url()) {
                $url->param('returnurl', $this->page->url->out_as_local_url(false));
            }
            $label = get_string('manageslides', 'block_slider');
            $this->content->footer = html_writer::link($url, $label, ['class' => 'btn btn-secondary btn-sm']);
        }

        return $this->content;
    }

    /**
     * Blok można dodać na każdej stronie.
     *
     * @return array
     */
    public function applicable_formats() {
        return ['all' => true];
    }

    /**
     * Wiele instancji na jednej stronie.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Brak ustawień globalnych.
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * Nagłówek tylko z własnym tytułem albo w trybie edycji.
     *
     * @return bool
     */
    public function hide_header() {
        return !$this->page->user_is_editing() && settings::from_config($this->config)['title'] === '';
    }

    /**
     * Klasy widoczności na urządzeniach i wyglądu bez ramki.
     *
     * @return array
     */
    public function html_attributes() {
        $attributes = parent::html_attributes();
        $settings = settings::from_config($this->config);
        if ($settings['frameless']) {
            $attributes['class'] .= ' block-slider-frameless';
        }
        if (!$this->page->user_is_editing()) {
            if ($settings['device'] === settings::DEVICE_DESKTOP) {
                $attributes['class'] .= ' d-none d-md-block';
            } else if ($settings['device'] === settings::DEVICE_MOBILE) {
                $attributes['class'] .= ' d-md-none';
            }
        }
        return $attributes;
    }

    /**
     * Usuwa slajdy razem z instancją bloku.
     *
     * @return bool
     */
    public function instance_delete() {
        slides::delete_all($this->instance->id);
        return true;
    }

    /**
     * Kopiuje slajdy przy kopiowaniu instancji (np. kokpit domyślny do kokpitu użytkownika).
     *
     * @param int $fromid źródłowa instancja bloku
     * @return bool
     */
    public function instance_copy($fromid) {
        slides::copy_all($fromid, $this->instance->id);
        return true;
    }

    /**
     * Ustawienia udostępniane aplikacji mobilnej i usługom sieciowym.
     *
     * @return stdClass
     */
    public function get_config_for_external() {
        return (object) [
            'instance' => (object) settings::from_config($this->config),
            'plugin' => new stdClass(),
        ];
    }
}
