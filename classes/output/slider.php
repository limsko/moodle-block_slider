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
use context_block;
use renderable;
use renderer_base;
use templatable;

/**
 * Slajder wyświetlany w bloku.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slider implements renderable, templatable {
    /** @var context_block Kontekst bloku. */
    protected $context;

    /** @var \stdClass[] Widoczne slajdy. */
    protected $slides;

    /** @var array Ustawienia z settings::from_config(). */
    protected $settings;

    /** @var string Identyfikator elementu w DOM. */
    protected $uniqueid;

    /**
     * Konstruktor.
     *
     * @param context_block $context kontekst bloku
     * @param \stdClass[] $slides slajdy do wyświetlenia
     * @param array $settings ustawienia instancji
     */
    public function __construct(context_block $context, array $slides, array $settings) {
        $this->context = $context;
        $this->slides = array_values($slides);
        $this->settings = $settings;
        $this->uniqueid = \html_writer::random_id('block-slider-');
    }

    /**
     * Identyfikator elementu w DOM.
     *
     * @return string
     */
    public function get_uniqueid(): string {
        return $this->uniqueid;
    }

    /**
     * Dane dla szablonu block_slider/slider.
     *
     * @param renderer_base $output renderer
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $s = $this->settings;
        $images = slides::get_images($this->context);
        $formatoptions = ['context' => $this->context];
        $overlay = settings::caption_overlay($s);

        $items = [];
        $total = 0;
        foreach ($this->slides as $slide) {
            if (empty($images[$slide->id][slides::AREA_IMAGE])) {
                continue;
            }
            $total++;
            $title = format_string((string) $slide->title, true, $formatoptions);
            $subtitle = format_string((string) $slide->subtitle, true, $formatoptions);
            $description = format_string((string) $slide->description, true, $formatoptions);
            $buttontext = format_string((string) $slide->buttontext, true, $formatoptions);
            $link = slides::link_url((string) $slide->link);
            $showbutton = $s['captions'] && $link && $buttontext !== '';
            $mobile = $images[$slide->id][slides::AREA_MOBILE] ?? null;
            $alt = trim((string) $slide->alttext) !== '' ? $slide->alttext : (string) $slide->title;

            $items[] = [
                'index' => $total,
                'first' => $total === 1,
                'imageurl' => slides::file_url($images[$slide->id][slides::AREA_IMAGE])->out(false),
                'mobileurl' => $mobile ? slides::file_url($mobile)->out(false) : '',
                'alt' => self::attribute_text($alt, $formatoptions),
                'link' => $link ? $link->out(false) : '',
                'wraplink' => $link && !$showbutton,
                'newwindow' => !empty($slide->newwindow),
                'title' => $title,
                'subtitle' => $subtitle,
                'description' => $description,
                'buttontext' => $buttontext,
                'showbutton' => $showbutton,
                'hascaption' => $s['captions'] && ($title !== '' || $subtitle !== '' || $description !== '' || $showbutton),
            ];
        }
        foreach ($items as &$item) {
            $item['label'] = get_string('slidexofy', 'block_slider', ['index' => $item['index'], 'total' => $total]);
            // Link bez tekstu potrzebuje nazwy dla czytników ekranu.
            $item['linklabel'] = ($item['wraplink'] && $item['alt'] === '' && !$item['hascaption'])
                ? get_string('slidenumber', 'block_slider', $item['index']) : '';
        }
        unset($item);

        $multiple = $total > 1;
        $arrows = $multiple && $s['arrows'];
        $pause = $multiple && $s['autoplay'] && $s['pausebutton'];
        $dots = $multiple && $s['dots'];
        $overlaycontrols = $s['controlsposition'] === 'overlay';

        return [
            'uniqueid' => $this->uniqueid,
            'regionlabel' => $s['title'] !== '' ? self::attribute_text($s['title'], $formatoptions)
                : get_string('sliderlabel', 'block_slider'),
            'classes' => implode(' ', $this->css_classes($overlay, $arrows && $overlaycontrols)),
            'style' => $this->css_style(),
            'config' => json_encode($this->js_config()),
            'slides' => $items,
            'hasslides' => $total > 0,
            'multiple' => $multiple,
            'overlaycontrols' => $overlaycontrols,
            'prevbutton' => $arrows ? self::control('prev', 'previousslide', 'block-slider-arrow block-slider-prev') : null,
            'nextbutton' => $arrows ? self::control('next', 'nextslide', 'block-slider-arrow block-slider-next') : null,
            'pausebutton' => $pause ? self::control('playpause', 'pause', 'block-slider-playpause') : null,
            'dots' => $dots,
            'controlbar' => !$overlaycontrols && ($arrows || $pause || $dots) || ($overlaycontrols && $dots),
        ];
    }

    /**
     * Dane przycisku sterowania dla szablonu block_slider/control.
     *
     * @param string $action akcja (prev, next, playpause)
     * @param string $label identyfikator etykiety
     * @param string $classes klasy CSS
     * @return array
     */
    protected static function control(string $action, string $label, string $classes): array {
        return [
            'action' => $action,
            'label' => get_string($label, 'block_slider'),
            'classes' => $classes,
            'isprev' => $action === 'prev',
            'isnext' => $action === 'next',
            'ispause' => $action === 'playpause',
        ];
    }

    /**
     * Tekst do atrybutu HTML (filtry wielojęzyczne bez znaczników, szablon sam go escapuje).
     *
     * @param string $text tekst
     * @param array $options opcje format_string()
     * @return string
     */
    protected static function attribute_text(string $text, array $options): string {
        return html_entity_decode(strip_tags(format_string($text, true, $options)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Klasy CSS głównego elementu odzwierciedlające ustawienia.
     *
     * @param bool $overlay czy napisy są nałożone na grafikę
     * @param bool $overlayarrows czy strzałki leżą na grafice
     * @return string[]
     */
    protected function css_classes(bool $overlay, bool $overlayarrows): array {
        $s = $this->settings;
        $classes = [
            'block-slider--' . $s['effect'],
            'block-slider--fit-' . $s['imagefit'],
            'block-slider--controls-' . $s['controlsposition'],
        ];
        if ($overlayarrows) {
            $classes[] = 'block-slider--has-arrows';
        }
        if (settings::css_ratio($s) === '') {
            $classes[] = 'block-slider--ratio-auto';
        }
        if ($s['mobileaspectratio'] !== '' && settings::css_ratio($s, true) === '') {
            $classes[] = 'block-slider--mobile-ratio-auto';
        }
        if ($s['captions']) {
            $classes[] = 'block-slider--caption-' . $s['captionstyle'];
            $classes[] = 'block-slider--caption-align-' . $s['captionalign'];
            $classes[] = 'block-slider--caption-bg-' . $s['captionbg'];
            if ($overlay) {
                $classes[] = 'block-slider--caption-overlay';
                $classes[] = 'block-slider--caption-' . $s['captionposition'];
                if ($s['captionhover']) {
                    $classes[] = 'block-slider--caption-hover';
                }
            }
        }
        return $classes;
    }

    /**
     * Zmienne CSS z ustawień (wartości są wcześniej zweryfikowane).
     *
     * @return string
     */
    protected function css_style(): string {
        $s = $this->settings;
        $vars = [
            '--block-slider-per-view' => $s['slidestoshow'],
            '--block-slider-gap' => $s['gap'] . 'px',
            '--block-slider-speed' => $s['speed'] . 'ms',
            '--block-slider-radius' => $s['rounded'] . 'px',
            '--block-slider-caption-scale' => settings::CAPTION_SIZES[$s['captionsize']],
            '--block-slider-caption-width' => $s['captionwidth'] . '%',
            '--block-slider-caption-alpha' => $s['captionopacity'] / 100,
        ];
        if (($ratio = settings::css_ratio($s)) !== '') {
            $vars['--block-slider-ratio'] = $ratio;
        }
        if (($ratio = settings::css_ratio($s, true)) !== '') {
            $vars['--block-slider-ratio-mobile'] = $ratio;
        }
        if ($s['customaccent']) {
            $vars['--block-slider-accent'] = $s['accentcolor'];
        }
        if ($s['maxwidth'] > 0) {
            $vars['max-width'] = $s['maxwidth'] . 'px';
        }

        $style = '';
        foreach ($vars as $name => $value) {
            $style .= $name . ': ' . $value . ';';
        }
        return $style;
    }

    /**
     * Konfiguracja modułu AMD block_slider/slider.
     *
     * @return array
     */
    protected function js_config(): array {
        $s = $this->settings;
        return [
            'effect' => $s['effect'],
            'perview' => $s['slidestoshow'],
            'autoplay' => $s['autoplay'],
            'interval' => $s['interval'],
            'loop' => $s['loop'],
            'pauseonhover' => $s['pauseonhover'],
            'swipe' => $s['swipe'],
            'strings' => [
                'play' => get_string('play', 'block_slider'),
                'pause' => get_string('pause', 'block_slider'),
                'gotoslide' => get_string('gotoslide', 'block_slider', '{n}'),
            ],
        ];
    }
}
