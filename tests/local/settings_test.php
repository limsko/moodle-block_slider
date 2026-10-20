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
 * Testy normalizacji ustawień instancji.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_slider\local\settings
 */
final class settings_test extends \basic_testcase {
    /**
     * Brak konfiguracji daje wartości domyślne.
     */
    public function test_defaults(): void {
        $values = settings::from_config(null);

        $this->assertSame(array_keys(settings::DEFAULTS), array_keys($values));
        $this->assertTrue($values['autoplay']);
        $this->assertFalse($values['customaccent']);
        $this->assertSame('slide', $values['effect']);
        $this->assertSame(5000, $values['interval']);
        $this->assertSame('16 / 9', settings::css_ratio($values));
        $this->assertSame('', settings::css_ratio($values, true));
    }

    /**
     * Wartości spoza zakresu i list są zastępowane poprawnymi.
     */
    public function test_invalid_values(): void {
        $values = settings::from_config((object) [
            'effect' => 'cube',
            'slidestoshow' => 20,
            'aspectratio' => '5:4',
            'interval' => 10,
            'speed' => 333,
            'gap' => 7,
            'rounded' => -5,
            'maxwidth' => -100,
            'device' => 9,
            'captionstyle' => 'neon',
            'captionposition' => 'left',
            'captionalign' => 'justify',
            'customaccent' => 1,
            'accentcolor' => 'red',
            'controlsposition' => 'left',
            'captionsize' => 'huge',
            'captionbg' => 'red',
            'captionopacity' => 33,
            'captionwidth' => 60,
            'unknown' => 'ignored',
        ]);

        $this->assertSame('bottom', $values['controlsposition']);
        $this->assertSame('medium', $values['captionsize']);
        $this->assertSame('dark', $values['captionbg']);
        $this->assertSame(70, $values['captionopacity']);
        $this->assertSame(75, $values['captionwidth']);

        $this->assertSame('slide', $values['effect']);
        $this->assertSame(settings::MAX_SLIDES_TO_SHOW, $values['slidestoshow']);
        $this->assertSame('16:9', $values['aspectratio']);
        $this->assertSame(settings::MIN_INTERVAL, $values['interval']);
        $this->assertSame(500, $values['speed']);
        $this->assertSame(16, $values['gap']);
        $this->assertSame(0, $values['rounded']);
        $this->assertSame(0, $values['maxwidth']);
        $this->assertSame(settings::DEVICE_ALL, $values['device']);
        $this->assertSame('gradient', $values['captionstyle']);
        $this->assertSame('bottom', $values['captionposition']);
        $this->assertSame('start', $values['captionalign']);
        $this->assertSame('', $values['accentcolor']);
        $this->assertFalse($values['customaccent']);
        $this->assertArrayNotHasKey('unknown', $values);
    }

    /**
     * Wartości z formularza (napisy) są rzutowane na właściwe typy.
     */
    public function test_form_values(): void {
        $values = settings::from_config((object) [
            'title' => '  Banners ',
            'slidestoshow' => '3',
            'gap' => '24',
            'speed' => '0',
            'autoplay' => '0',
            'customaccent' => '1',
            'accentcolor' => '#AA00cc',
            'aspectratio' => 'custom',
            'aspectwidth' => '940',
            'aspectheight' => '528',
            'mobileaspectratio' => '1:1',
        ]);

        $this->assertSame('Banners', $values['title']);
        $this->assertSame(3, $values['slidestoshow']);
        $this->assertSame(24, $values['gap']);
        $this->assertSame(0, $values['speed']);
        $this->assertFalse($values['autoplay']);
        $this->assertTrue($values['customaccent']);
        $this->assertSame('#aa00cc', $values['accentcolor']);
        $this->assertSame('940 / 528', settings::css_ratio($values));
        $this->assertSame('1 / 1', settings::css_ratio($values, true));
    }

    /**
     * Przenikanie zawsze pokazuje jeden slajd, a proporcje naturalne nie mają wartości CSS.
     */
    public function test_fade_and_auto_ratio(): void {
        $values = settings::from_config((object) ['effect' => 'fade', 'slidestoshow' => 4, 'aspectratio' => 'auto']);

        $this->assertSame(1, $values['slidestoshow']);
        $this->assertSame('', settings::css_ratio($values));
    }

    /**
     * Napisy na grafice tylko dla stylów nakładanych i włączonych napisów.
     */
    public function test_caption_overlay(): void {
        $this->assertTrue(settings::caption_overlay(settings::from_config((object) ['captionstyle' => 'solid'])));
        $this->assertFalse(settings::caption_overlay(settings::from_config((object) ['captionstyle' => 'side'])));
        $this->assertFalse(settings::caption_overlay(settings::from_config((object) ['captionstyle' => 'below'])));
        $this->assertFalse(settings::caption_overlay(settings::from_config((object) ['captions' => 0])));
    }

    /**
     * Rozpoznawanie koloru.
     */
    public function test_is_color(): void {
        $this->assertTrue(settings::is_color('#0f6cbf'));
        $this->assertTrue(settings::is_color('#ABCDEF'));
        $this->assertFalse(settings::is_color('#fff'));
        $this->assertFalse(settings::is_color('0f6cbf'));
        $this->assertFalse(settings::is_color('#0f6cbf;color:red'));
    }
}
