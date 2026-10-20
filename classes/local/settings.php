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

/**
 * Ustawienia instancji slajdera: wartości domyślne, normalizacja i listy opcji.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class settings {
    /** @var int Pokazuj na wszystkich urządzeniach. */
    public const DEVICE_ALL = 0;

    /** @var int Pokazuj tylko na szerszych ekranach. */
    public const DEVICE_DESKTOP = 1;

    /** @var int Pokazuj tylko na wąskich ekranach. */
    public const DEVICE_MOBILE = 2;

    /** @var array Wartości domyślne wszystkich ustawień. */
    public const DEFAULTS = [
        'title' => '',
        'device' => self::DEVICE_ALL,
        'frameless' => 0,
        'effect' => 'slide',
        'slidestoshow' => 1,
        'aspectratio' => '16:9',
        'aspectwidth' => 16,
        'aspectheight' => 9,
        'mobileaspectratio' => '',
        'imagefit' => 'cover',
        'maxwidth' => 0,
        'gap' => 16,
        'rounded' => 0,
        'autoplay' => 1,
        'interval' => 5000,
        'speed' => 500,
        'loop' => 1,
        'pauseonhover' => 1,
        'swipe' => 1,
        'controlsposition' => 'bottom',
        'arrows' => 1,
        'dots' => 1,
        'pausebutton' => 1,
        'customaccent' => 0,
        'accentcolor' => '',
        'captions' => 1,
        'captionstyle' => 'gradient',
        'captionposition' => 'bottom',
        'captionalign' => 'start',
        'captionwidth' => 75,
        'captionsize' => 'medium',
        'captionbg' => 'dark',
        'captionopacity' => 70,
        'captionhover' => 0,
    ];

    /** @var string[] Style napisów: na grafice (pierwsze cztery), pod grafiką i obok grafiki. */
    public const CAPTION_STYLES = ['gradient', 'solid', 'box', 'plain', 'below', 'side'];

    /** @var string[] Style napisów leżących na grafice. */
    public const OVERLAY_STYLES = ['gradient', 'solid', 'box', 'plain'];

    /** @var array Wielkości tekstu napisów i ich mnożniki. */
    public const CAPTION_SIZES = ['small' => 0.85, 'medium' => 1, 'large' => 1.2, 'xlarge' => 1.45];

    /** @var int[] Szerokości bloku napisów w %. */
    public const CAPTION_WIDTHS = [50, 75, 100];

    /** @var int[] Krycie apli napisów w %. */
    public const CAPTION_OPACITIES = [40, 55, 70, 85, 100];

    /** @var string[] Dostępne proporcje obrazu. */
    public const ASPECT_RATIOS = ['21:9', '16:9', '2:1', '3:1', '4:3', '1:1', 'auto', 'custom'];

    /** @var int[] Dostępne czasy przejścia w ms. */
    public const SPEEDS = [0, 250, 500, 750, 1000, 1500];

    /** @var int[] Dostępne odstępy między slajdami w px. */
    public const GAPS = [0, 8, 16, 24, 32];

    /** @var int[] Dostępne zaokrąglenia narożników w px. */
    public const RADII = [0, 4, 8, 16, 24];

    /** @var int Najmniejszy czas wyświetlania slajdu w ms. */
    public const MIN_INTERVAL = 1000;

    /** @var int Największa liczba slajdów widocznych naraz. */
    public const MAX_SLIDES_TO_SHOW = 6;

    /**
     * Zwraca kompletne, poprawne ustawienia na podstawie konfiguracji instancji.
     *
     * @param \stdClass|null $config konfiguracja bloku
     * @return array
     */
    public static function from_config(?\stdClass $config): array {
        $values = self::DEFAULTS;
        foreach ((array) $config as $key => $value) {
            if (array_key_exists($key, $values) && $value !== null) {
                $values[$key] = $value;
            }
        }

        $bools = ['frameless', 'autoplay', 'loop', 'pauseonhover', 'swipe', 'arrows', 'dots', 'pausebutton',
            'customaccent', 'captions', 'captionhover'];
        foreach ($bools as $key) {
            $values[$key] = !empty($values[$key]);
        }

        $values['title'] = trim((string) $values['title']);
        $devices = [self::DEVICE_ALL, self::DEVICE_DESKTOP, self::DEVICE_MOBILE];
        $values['device'] = self::pick((int) $values['device'], $devices, self::DEVICE_ALL);
        $values['effect'] = self::pick((string) $values['effect'], ['slide', 'fade'], 'slide');
        $values['slidestoshow'] = max(1, min(self::MAX_SLIDES_TO_SHOW, (int) $values['slidestoshow']));
        if ($values['effect'] === 'fade') {
            $values['slidestoshow'] = 1;
        }
        $values['aspectratio'] = self::pick((string) $values['aspectratio'], self::ASPECT_RATIOS, '16:9');
        $values['aspectwidth'] = max(1, (int) $values['aspectwidth']);
        $values['aspectheight'] = max(1, (int) $values['aspectheight']);
        $mobileratios = array_merge([''], array_diff(self::ASPECT_RATIOS, ['custom']));
        $values['mobileaspectratio'] = self::pick((string) $values['mobileaspectratio'], $mobileratios, '');
        $values['imagefit'] = self::pick((string) $values['imagefit'], ['cover', 'contain'], 'cover');
        $values['maxwidth'] = max(0, (int) $values['maxwidth']);
        $values['gap'] = self::pick((int) $values['gap'], self::GAPS, 16);
        $values['rounded'] = self::pick((int) $values['rounded'], self::RADII, 0);
        $values['interval'] = max(self::MIN_INTERVAL, (int) $values['interval']);
        $values['speed'] = self::pick((int) $values['speed'], self::SPEEDS, 500);
        $values['accentcolor'] = self::is_color((string) $values['accentcolor']) ? strtolower($values['accentcolor']) : '';
        if ($values['accentcolor'] === '') {
            $values['customaccent'] = false;
        }
        $values['controlsposition'] = self::pick((string) $values['controlsposition'], ['bottom', 'overlay'], 'bottom');
        $values['captionstyle'] = self::pick((string) $values['captionstyle'], self::CAPTION_STYLES, 'gradient');
        $values['captionposition'] = self::pick((string) $values['captionposition'], ['top', 'center', 'bottom'], 'bottom');
        $values['captionalign'] = self::pick((string) $values['captionalign'], ['start', 'center', 'end'], 'start');
        $values['captionwidth'] = self::pick((int) $values['captionwidth'], self::CAPTION_WIDTHS, 75);
        $sizes = array_keys(self::CAPTION_SIZES);
        $values['captionsize'] = self::pick((string) $values['captionsize'], $sizes, 'medium');
        $values['captionbg'] = self::pick((string) $values['captionbg'], ['dark', 'light', 'accent'], 'dark');
        $values['captionopacity'] = self::pick((int) $values['captionopacity'], self::CAPTION_OPACITIES, 70);

        return $values;
    }

    /**
     * Wartość proporcji obrazu w zapisie CSS (np. "16 / 9"), pusta dla proporcji naturalnych.
     *
     * @param array $values ustawienia z from_config()
     * @param bool $mobile proporcje dla wąskich ekranów
     * @return string
     */
    public static function css_ratio(array $values, bool $mobile = false): string {
        $ratio = $mobile ? $values['mobileaspectratio'] : $values['aspectratio'];
        if ($ratio === '' || $ratio === 'auto') {
            return '';
        }
        if ($ratio === 'custom') {
            return $values['aspectwidth'] . ' / ' . $values['aspectheight'];
        }
        return str_replace(':', ' / ', $ratio);
    }

    /**
     * Czy napisy leżą na grafice (a nie pod nią lub obok).
     *
     * @param array $values ustawienia z from_config()
     * @return bool
     */
    public static function caption_overlay(array $values): bool {
        return $values['captions'] && in_array($values['captionstyle'], self::OVERLAY_STYLES, true);
    }

    /**
     * Czy podany tekst jest kolorem w formacie #rrggbb.
     *
     * @param string $value wartość do sprawdzenia
     * @return bool
     */
    public static function is_color(string $value): bool {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $value);
    }

    /**
     * Opcje proporcji obrazu dla formularza.
     *
     * @param bool $mobile lista dla wąskich ekranów (z opcją "jak na desktopie", bez własnych)
     * @return array
     */
    public static function aspect_ratio_options(bool $mobile = false): array {
        $options = $mobile ? ['' => get_string('mobileaspectratio_same', 'block_slider')] : [];
        foreach (self::ASPECT_RATIOS as $ratio) {
            if ($mobile && $ratio === 'custom') {
                continue;
            }
            if ($ratio === 'auto' || $ratio === 'custom') {
                $options[$ratio] = get_string('aspectratio_' . $ratio, 'block_slider');
            } else {
                $options[$ratio] = $ratio;
            }
        }
        return $options;
    }

    /**
     * Opcje czasu przejścia dla formularza.
     *
     * @return array
     */
    public static function speed_options(): array {
        $options = [];
        foreach (self::SPEEDS as $speed) {
            $options[$speed] = $speed ? get_string('speedms', 'block_slider', $speed) : get_string('speed_none', 'block_slider');
        }
        return $options;
    }

    /**
     * Opcje w pikselach (odstęp, zaokrąglenie) dla formularza.
     *
     * @param int[] $values wartości
     * @return array
     */
    public static function pixel_options(array $values): array {
        $options = [];
        foreach ($values as $value) {
            $options[$value] = $value ? get_string('sizepx', 'block_slider', $value) : get_string('none');
        }
        return $options;
    }

    /**
     * Opcje w procentach (szerokość, krycie) dla formularza.
     *
     * @param int[] $values wartości
     * @return array
     */
    public static function percent_options(array $values): array {
        $options = [];
        foreach ($values as $value) {
            $options[$value] = get_string('percents', 'moodle', $value);
        }
        return $options;
    }

    /**
     * Zwraca wartość, jeśli jest na liście dozwolonych, w przeciwnym razie domyślną.
     *
     * @param mixed $value wartość
     * @param array $allowed dozwolone wartości
     * @param mixed $default wartość domyślna
     * @return mixed
     */
    private static function pick($value, array $allowed, $default) {
        return in_array($value, $allowed, true) ? $value : $default;
    }
}
