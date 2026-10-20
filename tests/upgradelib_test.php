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

namespace block_slider;

use block_slider\local\settings;
use block_slider\local\slides;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/blocks/slider/db/upgradelib.php');

/**
 * Testy przenoszenia danych ze starszych wersji bloku.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversFunction('block_slider_migrate_legacy_slides')]
#[\PHPUnit\Framework\Attributes\CoversFunction('block_slider_migrate_legacy_config')]
#[\PHPUnit\Framework\Attributes\CoversFunction('block_slider_convert_legacy_config')]
#[\PHPUnit\Framework\Attributes\CoversFunction('block_slider_legacy_ratio')]
#[\PHPUnit\Framework\Attributes\CoversFunction('block_slider_legacy_speed')]
#[\PHPUnit\Framework\Attributes\CoversFunction('block_slider_legacy_link')]
final class upgradelib_test extends \advanced_testcase {
    /**
     * Tymczasowa tabela w formacie wersji 0.6.
     */
    protected function create_legacy_table(): void {
        global $DB;

        $table = new \xmldb_table('slider_slides');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('sliderid', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('slide_link', XMLDB_TYPE_CHAR, '255');
        $table->add_field('slide_button', XMLDB_TYPE_CHAR, '150');
        $table->add_field('slide_title', XMLDB_TYPE_CHAR, '255');
        $table->add_field('slide_title2', XMLDB_TYPE_CHAR, '150');
        $table->add_field('slide_target', XMLDB_TYPE_CHAR, '150');
        $table->add_field('slide_desc', XMLDB_TYPE_CHAR, '255');
        $table->add_field('slide_order', XMLDB_TYPE_INTEGER, '10');
        $table->add_field('slide_image', XMLDB_TYPE_CHAR, '255');
        $table->add_field('slide_image_mobile', XMLDB_TYPE_CHAR, '255');
        $table->add_field('slide_alt', XMLDB_TYPE_CHAR, '255');
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $DB->get_manager()->create_temp_table($table);
    }

    /**
     * Stary slajd z grafikami w starych obszarach plików.
     *
     * @param \stdClass $block instancja bloku
     * @param array $fields pola rekordu
     * @param bool $mobile czy dodać grafikę mobilną
     * @return int identyfikator starego rekordu
     */
    protected function create_legacy_slide(\stdClass $block, array $fields, bool $mobile = false): int {
        global $DB;

        $fields['sliderid'] = $block->id;
        $fields['slide_image'] = 'banner.png';
        $fields['slide_image_mobile'] = $mobile ? 'phone.png' : null;
        $id = $DB->insert_record('slider_slides', (object) $fields);

        $context = \context_block::instance($block->id);
        $fs = get_file_storage();
        $record = ['contextid' => $context->id, 'component' => 'block_slider', 'itemid' => $id, 'filepath' => '/'];
        $desktop = $record + ['filearea' => 'slider_slides', 'filename' => 'banner.png'];
        $fs->create_file_from_pathname($desktop, __DIR__ . '/fixtures/slide1.png');
        if ($mobile) {
            $phone = $record + ['filearea' => 'slider_slides_mobile', 'filename' => 'phone.png'];
            $fs->create_file_from_pathname($phone, __DIR__ . '/fixtures/mobile.png');
        }
        return $id;
    }

    /**
     * Slajdy i pliki z tabeli slider_slides trafiają do nowej tabeli i obszarów.
     */
    public function test_migrate_legacy_slides(): void {
        global $DB;
        $this->resetAfterTest();
        $this->create_legacy_table();

        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $block = $generator->create_instance();
        $context = \context_block::instance($block->id);
        $this->create_legacy_slide($block, ['slide_title' => 'Second', 'slide_order' => 5, 'slide_target' => '_self',
            'slide_link' => 'course/index.php']);
        $this->create_legacy_slide($block, [
            'slide_title' => 'First',
            'slide_title2' => 'Sub',
            'slide_desc' => 'Desc',
            'slide_link' => 'https://moodle.org',
            'slide_button' => 'Go',
            'slide_target' => '_blank',
            'slide_alt' => 'Alt',
            'slide_order' => -3,
        ], true);
        // Slajd usuniętej instancji jest pomijany.
        $DB->insert_record('slider_slides', (object) ['sliderid' => 999999, 'slide_title' => 'Orphan', 'slide_order' => 0]);

        $this->assertSame(2, block_slider_migrate_legacy_slides());

        $list = array_values(slides::get_slides($block->id));
        $this->assertSame(['First', 'Second'], array_column($list, 'title'));
        $this->assertSame('Sub', $list[0]->subtitle);
        $this->assertSame('Desc', $list[0]->description);
        $this->assertSame('https://moodle.org', $list[0]->link);
        $this->assertSame('Go', $list[0]->buttontext);
        $this->assertSame('Alt', $list[0]->alttext);
        $this->assertEquals(1, $list[0]->newwindow);
        $this->assertEquals(0, $list[1]->newwindow);
        $this->assertSame('/course/index.php', $list[1]->link);
        $this->assertEquals(1, $list[1]->visible);

        $images = slides::get_images($context);
        $this->assertSame('banner.png', $images[$list[0]->id][slides::AREA_IMAGE]->get_filename());
        $this->assertSame('phone.png', $images[$list[0]->id][slides::AREA_MOBILE]->get_filename());
        $this->assertArrayNotHasKey(slides::AREA_MOBILE, $images[$list[1]->id]);

        $fs = get_file_storage();
        $this->assertTrue($fs->is_area_empty($context->id, 'block_slider', 'slider_slides'));
        $this->assertTrue($fs->is_area_empty($context->id, 'block_slider', 'slider_slides_mobile'));

        $DB->get_manager()->drop_table(new \xmldb_table('slider_slides'));
    }

    /**
     * Grafiki z obszaru "content" (najstarsze wersje) stają się slajdami.
     */
    public function test_migrate_content_area(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $block = $generator->create_instance();
        $context = \context_block::instance($block->id);
        $fs = get_file_storage();
        foreach (['b.png' => 'slide2.png', 'a.png' => 'slide1.png'] as $name => $fixture) {
            $record = [
                'contextid' => $context->id,
                'component' => 'block_slider',
                'filearea' => 'content',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $name,
            ];
            $fs->create_file_from_pathname($record, __DIR__ . '/fixtures/' . $fixture);
        }

        $this->assertSame(2, block_slider_migrate_legacy_slides());

        $list = array_values(slides::get_slides($block->id));
        $this->assertCount(2, $list);
        $images = slides::get_images($context);
        $this->assertSame('a.png', $images[$list[0]->id][slides::AREA_IMAGE]->get_filename());
        $this->assertTrue($fs->is_area_empty($context->id, 'block_slider', 'content'));
    }

    /**
     * Ustawienia BX Slider (wersja 0.4).
     */
    public function test_convert_bxslider_config(): void {
        $new = block_slider_convert_legacy_config((object) [
            'text' => 'Header',
            'width' => 940,
            'height' => 528,
            'slider_js' => 'bxslider',
            'interval' => '7000',
            'bx_effect' => 'horizontal',
            'bx_speed' => 800,
            'bx_captions' => 1,
            'bx_pager' => 0,
            'bx_controls' => 1,
            'bx_auto' => 0,
            'bx_hideonhover' => 1,
        ]);
        $values = settings::from_config($new);

        $this->assertSame('Header', $values['title']);
        $this->assertSame('16:9', $values['aspectratio']);
        $this->assertSame('slide', $values['effect']);
        $this->assertSame(750, $values['speed']);
        $this->assertSame(7000, $values['interval']);
        $this->assertTrue($values['captions']);
        $this->assertTrue($values['captionhover']);
        $this->assertFalse($values['dots']);
        $this->assertTrue($values['arrows']);
        $this->assertFalse($values['autoplay']);
    }

    /**
     * Ustawienia SlidesJS (wersja 0.4).
     */
    public function test_convert_slidesjs_config(): void {
        $values = settings::from_config(block_slider_convert_legacy_config((object) [
            'slider_js' => 'slides',
            'effect' => 'fade',
            'navigation' => 0,
            'pagination' => 1,
            'autoplay' => 1,
            'width' => 1200,
            'height' => 300,
        ]));

        $this->assertSame('fade', $values['effect']);
        $this->assertFalse($values['arrows']);
        $this->assertTrue($values['dots']);
        $this->assertTrue($values['autoplay']);
        $this->assertSame('custom', $values['aspectratio']);
        $this->assertSame('1200 / 300', settings::css_ratio($values));
    }

    /**
     * Ustawienia wersji opartej o Glider.js (0.6).
     */
    public function test_convert_glider_config(): void {
        $values = settings::from_config(block_slider_convert_legacy_config((object) [
            'text' => 'Promo',
            'device' => 2,
            'autoplay' => 1,
            'interval' => 6000,
            'duration' => '1.25',
            'rewind' => 0,
            'draggable' => 0,
            'slidestoshow' => 3,
            'arrows' => 1,
            'dots' => 0,
            'pausebutton' => 0,
            'pauseonhover' => 0,
            'accentcolor' => '#003480',
            'showcaptions' => 1,
            'captionhover' => 0,
            'width' => 1600,
            'height' => 800,
        ]));

        $this->assertSame('Promo', $values['title']);
        $this->assertSame(settings::DEVICE_MOBILE, $values['device']);
        $this->assertSame(1000, $values['speed']);
        $this->assertFalse($values['loop']);
        $this->assertFalse($values['swipe']);
        $this->assertSame(3, $values['slidestoshow']);
        $this->assertFalse($values['dots']);
        $this->assertFalse($values['pausebutton']);
        $this->assertFalse($values['pauseonhover']);
        $this->assertTrue($values['customaccent']);
        $this->assertSame('#003480', $values['accentcolor']);
        $this->assertTrue($values['captions']);
        $this->assertSame('2:1', $values['aspectratio']);
    }

    /**
     * Zapis przekonwertowanych ustawień i pomijanie instancji w nowym formacie.
     */
    public function test_migrate_legacy_config(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator()->get_plugin_generator('block_slider');
        $old = $generator->create_instance(['configdata' => base64_encode(serialize((object) ['text' => 'Old']))]);
        $current = $generator->create_instance(['title' => 'Current', 'captionstyle' => 'box']);
        $empty = $generator->create_instance();

        $this->assertSame(1, block_slider_migrate_legacy_config());

        $config = unserialize_object(base64_decode($DB->get_field('block_instances', 'configdata', ['id' => $old->id])));
        $this->assertSame('Old', $config->title);
        $this->assertSame('gradient', $config->captionstyle);
        $config = unserialize_object(base64_decode($DB->get_field('block_instances', 'configdata', ['id' => $current->id])));
        $this->assertSame('box', $config->captionstyle);
        $this->assertEmpty($DB->get_field('block_instances', 'configdata', ['id' => $empty->id]));
    }

    /**
     * Dopasowanie proporcji i czasu przejścia.
     */
    public function test_legacy_ratio_and_speed(): void {
        $this->assertSame(['16:9', 16, 9], block_slider_legacy_ratio(1920, 1080));
        $this->assertSame(['4:3', 4, 3], block_slider_legacy_ratio(800, 600));
        $this->assertSame(['3:1', 3, 1], block_slider_legacy_ratio(1000, 333));
        $this->assertSame(['custom', 1000, 370], block_slider_legacy_ratio(1000, 370));
        $this->assertSame(['16:9', 16, 9], block_slider_legacy_ratio(0, 100));
        $this->assertSame(0, block_slider_legacy_speed(0));
        $this->assertSame(1500, block_slider_legacy_speed(5000));
        $this->assertSame(250, block_slider_legacy_speed(300));
    }
}
