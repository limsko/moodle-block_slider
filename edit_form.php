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
 * Formularz ustawień instancji bloku slajdera.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_slider\local\settings;

/**
 * Ustawienia wyglądu i zachowania slajdera.
 *
 * @package     block_slider
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_slider_edit_form extends block_edit_form {
    /** @var MoodleQuickForm Formularz w trakcie budowy. */
    protected $mform;

    /**
     * Pola ustawień.
     *
     * @param MoodleQuickForm $mform formularz
     */
    protected function specific_definition($mform) {
        $this->mform = $mform;

        $this->general_fields();
        $this->layout_fields();
        $this->behaviour_fields();
        $this->controls_fields();
        $this->caption_fields();
    }

    /**
     * Sekcja ogólna: tytuł, urządzenia, ramka.
     */
    protected function general_fields(): void {
        $mform = $this->mform;
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        if (!empty($this->block->instance->id)) {
            $url = new moodle_url('/blocks/slider/manage.php', ['blockid' => $this->block->instance->id]);
            $link = html_writer::link($url, get_string('manageslides', 'block_slider'), ['class' => 'btn btn-secondary']);
            $mform->addElement('static', 'manageslides', '', $link);
        }

        $mform->addElement('text', 'config_title', get_string('configtitle', 'block_slider'), ['size' => 50]);
        $mform->setType('config_title', PARAM_TEXT);
        $mform->addRule('config_title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('config_title', 'configtitle', 'block_slider');

        $devices = [
            settings::DEVICE_ALL => get_string('device_all', 'block_slider'),
            settings::DEVICE_DESKTOP => get_string('device_desktop', 'block_slider'),
            settings::DEVICE_MOBILE => get_string('device_mobile', 'block_slider'),
        ];
        $this->add_select('device', $devices, true);
        $this->add_checkbox('frameless');
    }

    /**
     * Sekcja układu: przejście, proporcje, rozmiary.
     */
    protected function layout_fields(): void {
        $mform = $this->mform;
        $mform->addElement('header', 'layoutheader', get_string('layout', 'block_slider'));

        $effects = [
            'slide' => get_string('effect_slide', 'block_slider'),
            'fade' => get_string('effect_fade', 'block_slider'),
        ];
        $this->add_select('effect', $effects, true);

        $range = range(1, settings::MAX_SLIDES_TO_SHOW);
        $this->add_select('slidestoshow', array_combine($range, $range), true);
        $mform->hideIf('config_slidestoshow', 'config_effect', 'eq', 'fade');

        $this->add_select('aspectratio', settings::aspect_ratio_options(), true);

        $ratio = [
            $mform->createElement('text', 'config_aspectwidth', get_string('aspectwidth', 'block_slider'), ['size' => 4]),
            $mform->createElement('static', 'aspectseparator', '', ':'),
            $mform->createElement('text', 'config_aspectheight', get_string('aspectheight', 'block_slider'), ['size' => 4]),
        ];
        $mform->addGroup($ratio, 'aspectcustom', get_string('aspectcustom', 'block_slider'), ' ', false);
        $mform->setType('config_aspectwidth', PARAM_INT);
        $mform->setType('config_aspectheight', PARAM_INT);
        $mform->setDefault('config_aspectwidth', settings::DEFAULTS['aspectwidth']);
        $mform->setDefault('config_aspectheight', settings::DEFAULTS['aspectheight']);
        $mform->hideIf('aspectcustom', 'config_aspectratio', 'neq', 'custom');

        $this->add_select('mobileaspectratio', settings::aspect_ratio_options(true), true);

        $fits = [
            'cover' => get_string('imagefit_cover', 'block_slider'),
            'contain' => get_string('imagefit_contain', 'block_slider'),
        ];
        $this->add_select('imagefit', $fits, true);
        $mform->hideIf('config_imagefit', 'config_aspectratio', 'eq', 'auto');

        $mform->addElement('text', 'config_maxwidth', get_string('maxwidth', 'block_slider'), ['size' => 6]);
        $mform->setType('config_maxwidth', PARAM_INT);
        $mform->setDefault('config_maxwidth', settings::DEFAULTS['maxwidth']);
        $mform->addHelpButton('config_maxwidth', 'maxwidth', 'block_slider');

        $this->add_select('gap', settings::pixel_options(settings::GAPS));
        $mform->hideIf('config_gap', 'config_effect', 'eq', 'fade');

        $this->add_select('rounded', settings::pixel_options(settings::RADII));
    }

    /**
     * Sekcja zachowania: autoodtwarzanie, szybkość, pętla, gesty.
     */
    protected function behaviour_fields(): void {
        $mform = $this->mform;
        $mform->addElement('header', 'behaviourheader', get_string('behaviour', 'block_slider'));

        $this->add_checkbox('autoplay', true);

        $mform->addElement('text', 'config_interval', get_string('interval', 'block_slider'), ['size' => 6]);
        $mform->setType('config_interval', PARAM_INT);
        $mform->setDefault('config_interval', settings::DEFAULTS['interval']);
        $mform->addHelpButton('config_interval', 'interval', 'block_slider');
        $mform->hideIf('config_interval', 'config_autoplay', 'notchecked');

        $this->add_checkbox('pauseonhover');
        $mform->hideIf('config_pauseonhover', 'config_autoplay', 'notchecked');

        $this->add_select('speed', settings::speed_options(), true);
        $this->add_checkbox('loop');
        $this->add_checkbox('swipe');
    }

    /**
     * Sekcja sterowania: strzałki, kropki, pauza, kolor.
     */
    protected function controls_fields(): void {
        $mform = $this->mform;
        $mform->addElement('header', 'controlsheader', get_string('controls', 'block_slider'));

        $positions = [
            'bottom' => get_string('controlsposition_bottom', 'block_slider'),
            'overlay' => get_string('controlsposition_overlay', 'block_slider'),
        ];
        $this->add_select('controlsposition', $positions, true);
        $this->add_checkbox('arrows');
        $this->add_checkbox('dots');
        $this->add_checkbox('pausebutton', true);
        $mform->hideIf('config_pausebutton', 'config_autoplay', 'notchecked');
        $this->add_checkbox('customaccent');

        $attributes = ['size' => 8, 'placeholder' => '#0f6cbf'];
        $mform->addElement('text', 'config_accentcolor', get_string('accentcolor', 'block_slider'), $attributes);
        $mform->setType('config_accentcolor', PARAM_TEXT);
        $mform->addHelpButton('config_accentcolor', 'accentcolor', 'block_slider');
        $mform->hideIf('config_accentcolor', 'config_customaccent', 'notchecked');
    }

    /**
     * Sekcja napisów: styl, położenie, wyrównanie.
     */
    protected function caption_fields(): void {
        $mform = $this->mform;
        $mform->addElement('header', 'captionsheader', get_string('captions', 'block_slider'));

        $mform->addElement(
            'advcheckbox',
            'config_captions',
            get_string('showcaptions', 'block_slider'),
            get_string('showcaptions_desc', 'block_slider')
        );
        $mform->setDefault('config_captions', settings::DEFAULTS['captions']);
        $mform->addHelpButton('config_captions', 'showcaptions', 'block_slider');

        $notoverlay = array_values(array_diff(settings::CAPTION_STYLES, settings::OVERLAY_STYLES));

        $this->add_select('captionstyle', $this->options('captionstyle', settings::CAPTION_STYLES), true);
        $this->add_select('captionposition', $this->options('captionposition', ['top', 'center', 'bottom']));
        $mform->hideIf('config_captionposition', 'config_captionstyle', 'in', $notoverlay);
        $this->add_select('captionalign', $this->options('captionalign', ['start', 'center', 'end']));
        $this->add_select('captionwidth', settings::percent_options(settings::CAPTION_WIDTHS), true);
        $mform->hideIf('config_captionwidth', 'config_captionstyle', 'in', $notoverlay);
        $this->add_select('captionsize', $this->options('captionsize', array_keys(settings::CAPTION_SIZES)));

        $this->add_select('captionbg', $this->options('captionbg', ['dark', 'light', 'accent']), true);
        $mform->hideIf('config_captionbg', 'config_captionstyle', 'eq', 'below');
        $this->add_select('captionopacity', settings::percent_options(settings::CAPTION_OPACITIES), true);
        $mform->hideIf('config_captionopacity', 'config_captionstyle', 'in', array_merge($notoverlay, ['plain']));

        $this->add_checkbox('captionhover');
        $mform->hideIf('config_captionhover', 'config_captionstyle', 'in', $notoverlay);

        $fields = ['captionstyle', 'captionposition', 'captionalign', 'captionwidth', 'captionsize', 'captionbg',
            'captionopacity', 'captionhover'];
        foreach ($fields as $name) {
            $mform->hideIf('config_' . $name, 'config_captions', 'notchecked');
        }
    }

    /**
     * Opcje listy z etykietami {$prefix}_{wartość}.
     *
     * @param string $prefix prefiks identyfikatorów
     * @param string[] $values wartości
     * @return array
     */
    protected function options(string $prefix, array $values): array {
        $options = [];
        foreach ($values as $value) {
            $options[$value] = get_string($prefix . '_' . $value, 'block_slider');
        }
        return $options;
    }

    /**
     * Lista wyboru ustawienia z wartością domyślną.
     *
     * @param string $name nazwa ustawienia (bez prefiksu config_)
     * @param array $options opcje
     * @param bool $help czy dodać pomoc (string {$name}_help)
     */
    protected function add_select(string $name, array $options, bool $help = false): void {
        $this->mform->addElement('select', 'config_' . $name, get_string($name, 'block_slider'), $options);
        $this->mform->setDefault('config_' . $name, settings::DEFAULTS[$name]);
        if ($help) {
            $this->mform->addHelpButton('config_' . $name, $name, 'block_slider');
        }
    }

    /**
     * Pole wyboru tak/nie z opisem (string {$name}_desc) i wartością domyślną.
     *
     * @param string $name nazwa ustawienia (bez prefiksu config_)
     * @param bool $help czy dodać pomoc (string {$name}_help)
     */
    protected function add_checkbox(string $name, bool $help = false): void {
        $label = get_string($name, 'block_slider');
        $description = get_string($name . '_desc', 'block_slider');
        $this->mform->addElement('advcheckbox', 'config_' . $name, $label, $description);
        $this->mform->setDefault('config_' . $name, settings::DEFAULTS[$name]);
        if ($help) {
            $this->mform->addHelpButton('config_' . $name, $name, 'block_slider');
        }
    }

    /**
     * Walidacja ustawień liczbowych i koloru.
     *
     * @param array $data dane formularza
     * @param array $files pliki
     * @return array błędy
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!empty($data['config_autoplay']) && (int) ($data['config_interval'] ?? 0) < settings::MIN_INTERVAL) {
            $errors['config_interval'] = get_string('error_interval', 'block_slider', settings::MIN_INTERVAL);
        }
        if ((int) ($data['config_maxwidth'] ?? 0) < 0) {
            $errors['config_maxwidth'] = get_string('error_positive', 'block_slider');
        }
        $customratio = ($data['config_aspectratio'] ?? '') === 'custom';
        $width = (int) ($data['config_aspectwidth'] ?? 0);
        $height = (int) ($data['config_aspectheight'] ?? 0);
        if ($customratio && ($width < 1 || $height < 1)) {
            $errors['aspectcustom'] = get_string('error_positive', 'block_slider');
        }
        $color = trim((string) ($data['config_accentcolor'] ?? ''));
        if (!empty($data['config_customaccent']) && !settings::is_color($color)) {
            $errors['config_accentcolor'] = get_string('error_color', 'block_slider');
        }

        return $errors;
    }
}
