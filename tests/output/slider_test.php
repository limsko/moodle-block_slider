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

use block_slider\local\settings;
use block_slider\local\slides;

/**
 * Testy danych slajdera i panelu zarządzania dla szablonów.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers      \block_slider\output\slider
 * @covers      \block_slider\output\manage_page
 * @covers      \block_slider\output\renderer
 */
final class slider_test extends \advanced_testcase {
    /**
     * Blok z trzema slajdami o różnych ustawieniach.
     *
     * @return array [kontekst bloku, slajdy]
     */
    protected function create_slider(): array {
        global $PAGE;

        $PAGE->set_url('/');
        $PAGE->set_context(\context_system::instance());
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $block = $generator->create_instance();
        $slides = [
            $generator->create_slide(['blockinstanceid' => $block->id, 'title' => 'Linked', 'link' => '/course/index.php',
                'newwindow' => 1]),
            $generator->create_slide(['blockinstanceid' => $block->id, 'title' => 'Button', 'link' => 'https://moodle.org',
                'buttontext' => 'Read more', 'alttext' => 'Alt "quoted"',
                'mobileimage' => __DIR__ . '/../fixtures/mobile.png']),
            $generator->create_slide(['blockinstanceid' => $block->id, 'image' => __DIR__ . '/../fixtures/slide2.png']),
        ];
        return [\context_block::instance($block->id), $slides];
    }

    /**
     * Dane szablonu przy ustawieniach domyślnych.
     */
    public function test_export_defaults(): void {
        global $PAGE, $CFG;
        $this->resetAfterTest();
        [$context, $created] = $this->create_slider();

        $slider = new slider($context, slides::get_slides($context->instanceid, true), settings::from_config(null));
        $data = $slider->export_for_template($PAGE->get_renderer('block_slider'));

        $this->assertTrue($data['multiple']);
        $this->assertSame('prev', $data['prevbutton']['action']);
        $this->assertSame('next', $data['nextbutton']['action']);
        $this->assertTrue($data['pausebutton']['ispause']);
        $this->assertTrue($data['dots']);
        $this->assertTrue($data['controlbar']);
        $this->assertFalse($data['overlaycontrols']);
        $this->assertCount(3, $data['slides']);
        $this->assertStringStartsWith('block-slider-', $data['uniqueid']);
        $this->assertStringContainsString('--block-slider-ratio: 16 / 9;', $data['style']);
        $this->assertStringContainsString('--block-slider-caption-scale: 1;', $data['style']);
        $this->assertStringContainsString('--block-slider-caption-alpha: 0.7;', $data['style']);
        $this->assertStringNotContainsString('--block-slider-accent', $data['style']);
        $this->assertStringContainsString('block-slider--caption-gradient', $data['classes']);
        $this->assertStringContainsString('block-slider--caption-overlay', $data['classes']);
        $this->assertStringContainsString('block-slider--controls-bottom', $data['classes']);
        $this->assertStringNotContainsString('block-slider--has-arrows', $data['classes']);
        $config = json_decode($data['config'], true);
        $this->assertSame(5000, $config['interval']);
        $this->assertStringContainsString('{n}', $config['strings']['gotoslide']);

        [$first, $second, $third] = $data['slides'];
        $this->assertTrue($first['first']);
        $this->assertSame($CFG->wwwroot . '/course/index.php', $first['link']);
        $this->assertTrue($first['wraplink']);
        $this->assertTrue($first['newwindow']);
        $this->assertSame('Linked', $first['alt']);
        $this->assertSame('1 of 3', $first['label']);

        $this->assertFalse($second['wraplink']);
        $this->assertTrue($second['showbutton']);
        $this->assertSame('Alt "quoted"', $second['alt']);
        $this->assertStringContainsString('/slidemobile/' . $created[1]->id . '/mobile.png', $second['mobileurl']);

        $this->assertSame('', $third['alt']);
        $this->assertFalse($third['hascaption']);
        $this->assertSame('', $third['linklabel']);
        $this->assertStringContainsString('/slide/' . $created[2]->id . '/slide2.png', $third['imageurl']);
    }

    /**
     * Ustawienia własne przekładają się na klasy, zmienne CSS i sterowanie.
     */
    public function test_export_custom_settings(): void {
        global $PAGE;
        $this->resetAfterTest();
        [$context] = $this->create_slider();

        $settings = settings::from_config((object) [
            'title' => 'Banners',
            'effect' => 'fade',
            'autoplay' => 0,
            'arrows' => 0,
            'controlsposition' => 'overlay',
            'customaccent' => 1,
            'accentcolor' => '#112233',
            'maxwidth' => 800,
            'aspectratio' => 'auto',
            'captions' => 0,
        ]);
        $slider = new slider($context, slides::get_slides($context->instanceid, true), $settings);
        $data = $slider->export_for_template($PAGE->get_renderer('block_slider'));

        $this->assertSame('Banners', $data['regionlabel']);
        $this->assertNull($data['prevbutton']);
        $this->assertNull($data['pausebutton']);
        $this->assertTrue($data['overlaycontrols']);
        $this->assertTrue($data['controlbar']);
        $this->assertStringContainsString('block-slider--fade', $data['classes']);
        $this->assertStringContainsString('block-slider--ratio-auto', $data['classes']);
        $this->assertStringNotContainsString('caption', $data['classes']);
        $this->assertStringContainsString('--block-slider-accent: #112233;', $data['style']);
        $this->assertStringContainsString('max-width: 800px;', $data['style']);
        $this->assertStringNotContainsString('--block-slider-ratio:', $data['style']);
        // Bez napisów przycisk znika, a link obejmuje cały slajd.
        $this->assertTrue($data['slides'][1]['wraplink']);
        $this->assertFalse($data['slides'][1]['hascaption']);
    }

    /**
     * Jeden slajd bez sterowania, slajd bez grafiki jest pomijany, a brak slajdów nie renderuje niczego.
     */
    public function test_single_and_empty(): void {
        global $PAGE;
        $this->resetAfterTest();
        [$context, $created] = $this->create_slider();
        get_file_storage()->delete_area_files($context->id, 'block_slider', slides::AREA_IMAGE, $created[0]->id);
        slides::set_visible($created[2], false);

        $renderer = $PAGE->get_renderer('block_slider');
        $slider = new slider($context, slides::get_slides($context->instanceid, true), settings::from_config(null));
        $data = $slider->export_for_template($renderer);
        $this->assertCount(1, $data['slides']);
        $this->assertFalse($data['multiple']);
        $this->assertFalse($data['dots']);
        $this->assertSame('1 of 1', $data['slides'][0]['label']);

        $html = $renderer->render($slider);
        $this->assertStringContainsString('block-slider-slide', $html);
        $this->assertStringNotContainsString('data-action="next"', $html);

        $empty = new slider($context, [], settings::from_config(null));
        $this->assertSame('', $renderer->render($empty));
    }

    /**
     * Panel zarządzania pokazuje wszystkie slajdy z akcjami.
     */
    public function test_manage_page(): void {
        global $PAGE;
        $this->resetAfterTest();
        [$context, $created] = $this->create_slider();
        slides::set_visible($created[2], false);

        $page = new manage_page($context->instanceid, $context, new \moodle_url('/my/'));
        $data = $page->export_for_template($PAGE->get_renderer('block_slider'));

        $this->assertTrue($data['hasslides']);
        $this->assertTrue($data['sortable']);
        $this->assertCount(3, $data['slides']);
        $this->assertSame('', $data['filtercode']);
        $this->assertStringContainsString('returnurl=%2Fmy%2F', $data['addurl']);

        [$first, $second, $third] = $data['slides'];
        $this->assertSame(1, $first['position']);
        $this->assertStringContainsString('preview=bigthumb', $first['thumburl']);
        $this->assertSame('', $first['mobilethumburl']);
        $this->assertStringContainsString('preview=thumb', $second['mobilethumburl']);
        $this->assertSame('Button: Read more', $second['buttonlabel']);
        $this->assertSame('moodle.org', $second['linkshort']);
        $this->assertTrue($third['untitled']);
        $this->assertFalse($third['visible']);
        $this->assertStringContainsString('action=show', $third['toggleurl']);
        $this->assertStringContainsString('sesskey=', $third['moveupurl']);
    }

    /**
     * Skracanie adresów w panelu.
     */
    public function test_short_url(): void {
        $this->assertSame('moodle.org/plugins', manage_page::short_url('https://www.moodle.org/plugins/'));
        $this->assertSame('example.com/a?...', manage_page::short_url('http://example.com/a?b=1'));
        $this->assertSame('example.com', manage_page::short_url('https://example.com'));
    }
}
