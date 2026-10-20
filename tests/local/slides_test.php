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
 * Testy operacji na slajdach.
 *
 * @package     block_slider
 * @category    test
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[\PHPUnit\Framework\Attributes\CoversClass(slides::class)]
final class slides_test extends \advanced_testcase {
    /**
     * Generator bloku slajdera.
     *
     * @return \block_slider_generator
     */
    protected function generator(): \block_slider_generator {
        return $this->getDataGenerator()->get_plugin_generator('block_slider');
    }

    /**
     * Instancja bloku na stronie głównej z podaną liczbą slajdów.
     *
     * @param int $count liczba slajdów
     * @return array [instancja bloku, slajdy]
     */
    protected function create_slider(int $count): array {
        $block = $this->generator()->create_instance();
        $created = [];
        for ($i = 1; $i <= $count; $i++) {
            $created[] = $this->generator()->create_slide(['blockinstanceid' => $block->id, 'title' => 'Slide ' . $i]);
        }
        return [$block, $created];
    }

    /**
     * Nowe slajdy trafiają na koniec, a lista zwraca je w kolejności.
     */
    public function test_create_and_get_slides(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(3);

        $list = array_values(slides::get_slides($block->id));
        $this->assertCount(3, $list);
        $this->assertSame(['Slide 1', 'Slide 2', 'Slide 3'], array_column($list, 'title'));
        $this->assertEquals([0, 1, 2], array_map('intval', array_column($list, 'sortorder')));
        $this->assertEquals(1, $list[0]->visible);
        $this->assertGreaterThan(0, $list[0]->timecreated);
        $this->assertEquals($created[1]->id, slides::get_slide($created[1]->id, $block->id)->id);
    }

    /**
     * Slajd z innej instancji nie jest zwracany.
     */
    public function test_get_slide_from_other_block(): void {
        $this->resetAfterTest();
        [, $created] = $this->create_slider(1);
        [$other] = $this->create_slider(0);

        $this->expectException(\dml_missing_record_exception::class);
        slides::get_slide($created[0]->id, $other->id);
    }

    /**
     * Aktualizacja zapisuje tylko dozwolone pola.
     */
    public function test_update(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(1);

        $updated = slides::update($created[0], (object) [
            'title' => ' New title ',
            'link' => '/course/index.php',
            'newwindow' => 'yes',
            'sortorder' => 99,
            'blockinstanceid' => 12345,
        ]);
        $this->assertSame('New title', $updated->title);
        $this->assertSame('/course/index.php', $updated->link);
        $this->assertEquals(1, $updated->newwindow);
        $this->assertEquals(0, $updated->sortorder);
        $this->assertEquals($block->id, $updated->blockinstanceid);
    }

    /**
     * Przesuwanie w górę i w dół oraz brak zmian na krańcach listy.
     */
    public function test_move(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(3);

        $this->assertTrue(slides::move($created[2], 'up'));
        $this->assertSame(['Slide 1', 'Slide 3', 'Slide 2'], array_column(slides::get_slides($block->id), 'title'));

        $this->assertFalse(slides::move($created[0], 'up'));
        $this->assertTrue(slides::move($created[0], 'down'));
        $this->assertSame(['Slide 3', 'Slide 1', 'Slide 2'], array_column(slides::get_slides($block->id), 'title'));
    }

    /**
     * Zmiana kolejności z listy identyfikatorów pomija obce slajdy i dopisuje brakujące.
     */
    public function test_reorder(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(3);
        [, $foreign] = $this->create_slider(1);

        $ids = slides::reorder($block->id, [$created[2]->id, $foreign[0]->id, $created[0]->id]);
        $this->assertSame([(int) $created[2]->id, (int) $created[0]->id, (int) $created[1]->id], $ids);
        $this->assertSame(['Slide 3', 'Slide 1', 'Slide 2'], array_column(slides::get_slides($block->id), 'title'));
    }

    /**
     * Powtórzone wartości kolejności są porządkowane.
     */
    public function test_normalise_order(): void {
        global $DB;
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(3);
        $DB->set_field(slides::TABLE, 'sortorder', 5, ['blockinstanceid' => $block->id]);

        $ids = slides::normalise_order($block->id);
        $this->assertSame(array_map('intval', array_column($created, 'id')), $ids);
        $this->assertEquals([0, 1, 2], array_map('intval', array_column(slides::get_slides($block->id), 'sortorder')));
    }

    /**
     * Ukryte slajdy nie są zwracane jako widoczne.
     */
    public function test_set_visible(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(2);

        slides::set_visible($created[0], false);
        $this->assertCount(2, slides::get_slides($block->id));
        $this->assertSame(['Slide 2'], array_column(slides::get_slides($block->id, true), 'title'));

        slides::set_visible($created[0], true);
        $this->assertCount(2, slides::get_slides($block->id, true));
    }

    /**
     * Usunięcie slajdu usuwa grafiki i porządkuje kolejność.
     */
    public function test_delete(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(3);
        $context = \context_block::instance($block->id);
        $fs = get_file_storage();

        slides::delete($created[0]);

        $this->assertTrue($fs->is_area_empty($context->id, 'block_slider', slides::AREA_IMAGE, $created[0]->id));
        $this->assertFalse($fs->is_area_empty($context->id, 'block_slider', slides::AREA_IMAGE, $created[1]->id));
        $list = array_values(slides::get_slides($block->id));
        $this->assertSame(['Slide 2', 'Slide 3'], array_column($list, 'title'));
        $this->assertEquals([0, 1], array_map('intval', array_column($list, 'sortorder')));
    }

    /**
     * Usunięcie wszystkich slajdów instancji nie dotyka innych instancji.
     */
    public function test_delete_all(): void {
        $this->resetAfterTest();
        [$block] = $this->create_slider(2);
        [$other] = $this->create_slider(1);

        slides::delete_all($block->id);

        $this->assertEmpty(slides::get_slides($block->id));
        $this->assertEmpty(slides::get_images(\context_block::instance($block->id)));
        $this->assertCount(1, slides::get_slides($other->id));
    }

    /**
     * Kopiowanie przenosi slajdy z grafikami do nowej instancji.
     */
    public function test_copy_all(): void {
        $this->resetAfterTest();
        [$block] = $this->create_slider(2);
        $this->generator()->create_slide([
            'blockinstanceid' => $block->id,
            'title' => 'Mobile',
            'mobileimage' => __DIR__ . '/../fixtures/mobile.png',
        ]);
        [$target] = $this->create_slider(0);

        $this->assertSame(3, slides::copy_all($block->id, $target->id));

        $copies = slides::get_slides($target->id);
        $this->assertSame(['Slide 1', 'Slide 2', 'Mobile'], array_column($copies, 'title'));
        $images = slides::get_images(\context_block::instance($target->id));
        $this->assertCount(3, $images);
        $last = end($copies);
        $this->assertSame('mobile.png', $images[$last->id][slides::AREA_MOBILE]->get_filename());
    }

    /**
     * Grafiki są grupowane według slajdu i obszaru.
     */
    public function test_get_images_and_file_url(): void {
        $this->resetAfterTest();
        [$block, $created] = $this->create_slider(1);
        $context = \context_block::instance($block->id);

        $images = slides::get_images($context);
        $file = $images[$created[0]->id][slides::AREA_IMAGE];
        $this->assertSame('slide1.png', $file->get_filename());
        $this->assertArrayNotHasKey(slides::AREA_MOBILE, $images[$created[0]->id]);

        $url = slides::file_url($file, 'thumb')->out(false);
        $this->assertStringContainsString('/pluginfile.php/' . $context->id . '/block_slider/slide/' . $created[0]->id .
            '/slide1.png', $url);
        $this->assertStringContainsString('preview=thumb', $url);
    }

    /**
     * Poprawne i niepoprawne linki.
     *
     * @param string $link link
     * @param bool $valid oczekiwany wynik
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('link_provider')]
    public function test_is_valid_link(string $link, bool $valid): void {
        $this->assertSame($valid, slides::is_valid_link($link));
    }

    /**
     * Dane do test_is_valid_link.
     *
     * @return array
     */
    public static function link_provider(): array {
        return [
            'empty' => ['', true],
            'absolute https' => ['https://moodle.org/plugins/block_slider', true],
            'absolute http with query' => ['http://example.com/a?b=1&c=2', true],
            'local path' => ['/course/view.php?id=2', true],
            'protocol relative' => ['//example.com/x', false],
            'javascript' => ['javascript:alert(1)', false],
            'relative without slash' => ['course/view.php', false],
            'ftp' => ['ftp://example.com/file', false],
        ];
    }

    /**
     * Ścieżki lokalne są liczone od wwwroot.
     */
    public function test_link_url(): void {
        global $CFG;
        $this->resetAfterTest();

        $this->assertNull(slides::link_url(''));
        $this->assertSame($CFG->wwwroot . '/course/view.php?id=2', slides::link_url('/course/view.php?id=2')->out(false));
        $this->assertSame($CFG->wwwroot . '/my/', slides::link_url('my/')->out(false));
        $this->assertSame('https://moodle.org/', slides::link_url('https://moodle.org/')->out(false));
    }

    /**
     * Zarządzanie: uprawnienie bloku albo właściciel kokpitu.
     */
    public function test_can_manage(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_and_enrol($course, 'editingteacher');
        $student = $this->getDataGenerator()->create_and_enrol($course, 'student');
        $coursecontext = \context_course::instance($course->id);
        $block = $this->generator()->create_instance(['parentcontextid' => $coursecontext->id]);
        $context = \context_block::instance($block->id);

        $this->setUser($teacher);
        $this->assertTrue(slides::can_manage($context));
        $this->setUser($student);
        $this->assertFalse(slides::can_manage($context));

        $owner = $this->getDataGenerator()->create_user();
        $dashboard = $this->generator()->create_instance([
            'parentcontextid' => \context_user::instance($owner->id)->id,
            'pagetypepattern' => 'my-index',
        ]);
        $dashboardcontext = \context_block::instance($dashboard->id);
        $this->setUser($owner);
        $this->assertTrue(slides::can_manage($dashboardcontext));
        $this->setUser($student);
        $this->assertFalse(slides::can_manage($dashboardcontext));

        $this->expectException(\required_capability_exception::class);
        slides::require_manage($dashboardcontext);
    }
}
