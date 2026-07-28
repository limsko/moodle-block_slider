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
 * Simple slider block for Moodle
 *
 * If You like my plugin please send a small donation https://paypal.me/limsko Thanks!
 *
 * @package   block_slider
 * @copyright 2015-2020 Kamil Łuczak    www.limsko.pl     kamil@limsko.pl
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); // It must be included from a Moodle page.
}

/**
 * Class block_slider
 */
class block_slider extends block_base {

    public $hasslides = false;

    /**
     * Initializes block.
     *
     * @throws coding_exception
     */
    public function init() {
        global $DB;
        $this->title = get_string('pluginname', 'block_slider');
    }

    /**
     * Returns content of block.
     *
     * @return stdClass|stdObject
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_content() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->dirroot . '/blocks/slider/lib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        $this->hasslides = $DB->get_records('slider_slides', ['sliderid' => $this->instance->id], 'slide_order ASC') ?: [];

        $config = $this->config ?? new stdClass();

        $bxslider = isset($config->slider_js) && trim($config->slider_js) === 'bxslider';

        if (!empty($config->text)) {
            $this->content->text .= format_text($config->text, FORMAT_HTML, ['context' => $this->context, 'filter' => true]);
        }

        $imageshtml = $this->display_images($bxslider);
        if ($imageshtml !== '') {
            static $rendercounter = 0;
            $rendercounter++;
            $uniqueid = $this->instance->id . $rendercounter;
            $sliderdomid = 'slides' . $uniqueid;

            $sliderattrs = ['id' => $sliderdomid];
            if ($bxslider) {
                $sliderattrs['class'] = 'bxslider bxslider' . $uniqueid;
                $sliderattrs['style'] = 'visibility: hidden;';
            } else {
                $sliderattrs['class'] = 'slides' . $uniqueid;
                $sliderattrs['style'] = 'display: none;';
            }

            $sliderinner = html_writer::tag('div', $imageshtml, $sliderattrs);

            if (!empty($config->navigation) && !$bxslider && !empty($this->hasslides)) {
                $previcon = html_writer::tag('span', '', ['class' => 'icon fa fa-chevron-left', 'aria-hidden' => 'true']);
                $nexticon = html_writer::tag('span', '', ['class' => 'icon fa fa-chevron-right', 'aria-hidden' => 'true']);
                $prev = html_writer::link('#', $previcon . html_writer::span(get_string('previous', 'moodle'), 'sr-only'), [
                    'class' => 'slidesjs-previous slidesjs-navigation',
                    'role' => 'button',
                    'aria-label' => get_string('previous', 'moodle'),
                    'tabindex' => '0',
                ]);
                $next = html_writer::link('#', $nexticon . html_writer::span(get_string('next', 'moodle'), 'sr-only'), [
                    'class' => 'slidesjs-next slidesjs-navigation',
                    'role' => 'button',
                    'aria-label' => get_string('next', 'moodle'),
                    'tabindex' => '0',
                ]);
                $sliderinner .= $prev . $next;
            }

            $this->content->text .= html_writer::div($sliderinner, 'slider');

            $width = (!empty($config->width) && is_numeric($config->width)) ? (int) $config->width : 940;
            $height = (!empty($config->height) && is_numeric($config->height)) ? (int) $config->height : 528;
            $interval = (!empty($config->interval) && is_numeric($config->interval)) ? (int) $config->interval : 5000;
            $effect = !empty($config->effect) ? $config->effect : 'fade';
            $pag = !empty($config->pagination);
            $autoplay = !empty($config->autoplay);
            $nav = !empty($config->navigation) && !$bxslider;

            if ($bxslider) {
                $this->page->requires->js_call_amd('block_slider/bxslider', 'init',
                        bxslider_get_settings($config, $uniqueid));
            } else {
                $this->page->requires->js_call_amd('block_slider/slides', 'init',
                        [$width, $height, $effect, $interval, $autoplay, $pag, $nav, $uniqueid]);
            }
        } else if (has_capability('block/slider:manage', $this->context)) {
            $this->content->text .= html_writer::div(get_string('noimages', 'block_slider'), 'alert alert-info');
        }

        if (block_slider_user_can_manage($this->context)) {
            $instancearray = ['sliderid' => $this->instance->id];
            if (!empty($this->page->course->id)) {
                $instancearray['course'] = $this->page->course->id;
            }
            $editurl = new moodle_url('/blocks/slider/manage_images.php', $instancearray);
            $this->content->footer = html_writer::link($editurl, get_string('manage_slides', 'block_slider'), ['class' => 'btn btn-primary']);
        }

        return $this->content;
    }

    /**
     * Generate html with slides.
     *
     * @param bool $bxslider
     * @return string
     */
    public function display_images($bxslider = false) {
        if (empty($this->hasslides)) {
            return '';
        }

        $config = $this->config ?? new stdClass();

        $html = '';
        foreach ($this->hasslides as $slide) {
            $imageurl = moodle_url::make_pluginfile_url(
                $this->context->id,
                'block_slider',
                'slider_slides',
                $slide->id,
                '/',
                $slide->slide_image
            )->out(false);

            if ($bxslider) {
                $html .= html_writer::start_tag('div', ['class' => 'bxslide']);
            }

            $link = '';
            if (!empty($slide->slide_link)) {
                $cleanlink = clean_param($slide->slide_link, PARAM_URL);
                if (!empty($cleanlink)) {
                    $link = $cleanlink;
                    $html .= html_writer::start_tag('a', ['href' => $link, 'rel' => 'nofollow']);
                }
            }

            $alttext = !empty($slide->slide_title) ? format_string($slide->slide_title, true, ['context' => $this->context]) : s($slide->slide_image);
            $html .= html_writer::empty_tag('img', [
                'src' => $imageurl,
                'class' => 'img',
                'alt' => $alttext,
                'width' => '100%'
            ]);

            if ($link) {
                $html .= html_writer::end_tag('a');
            }

            if ($bxslider) {
                $showcaptions = !empty($config->bx_captions) || !empty($config->bx_displaydesc);
                if ($showcaptions) {
                    $classes = '';
                    if (!empty($config->bx_captions)) {
                        $classes .= ' bxcaption';
                    }
                    if (!empty($config->bx_displaydesc)) {
                        $classes .= ' bxdesc';
                    }
                    if (!empty($config->bx_hideonhover)) {
                        $classes .= ' hideonhover';
                    }
                    $html .= html_writer::start_tag('div', ['class' => 'bx-caption' . $classes]);
                    $titletext = trim((string) ($slide->slide_title ?? ''));
                    $desctext = trim((string) ($slide->slide_desc ?? ''));
                    if ($titletext !== '') {
                        $html .= html_writer::tag('span', format_string($titletext, true, ['context' => $this->context]));
                    }
                    if ($desctext !== '') {
                        $html .= html_writer::tag('p', format_string($desctext, true, ['context' => $this->context]));
                    }
                    $html .= html_writer::end_tag('div');
                }

                $html .= html_writer::end_tag('div');
            }
        }

        return $html;
    }

    /**
     * This plugin has no global config.
     *
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * We are legion.
     *
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
     * Where we can add the block?
     *
     * @return array
     */
    public function applicable_formats() {
        return array(
                'site' => true,
                'course-view' => true,
                'my' => true
        );
    }

    /**
     * What happens when instance of block is deleted.
     *
     * @return bool
     * @throws dml_exception
     */
    public function instance_delete() {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/blocks/slider/lib.php');
        if ($slides = $DB->get_records('slider_slides', array('sliderid' => $this->instance->id))) {
            foreach ($slides as $slide) {
                block_slider_delete_slide($slide);
            }
        }
        return true;
    }

    /**
     * Hide header of this block when user is not editing.
     *
     * @return bool
     */
    public function hide_header() {
        if ($this->page->user_is_editing()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Copy any block-specific data when copying to a new block instance.
     * @param int $fromid the id number of the block instance to copy from
     * @return boolean
     */
    public function instance_copy($fromid) {
        global $DB;
        $fromcontext = context_block::instance($fromid);
        $fs = get_file_storage();
        // Do not use draft files hacks outside of forms.
        if ($slides = $DB->get_records('slider_slides', array('sliderid' => $fromid), 'slide_order ASC')) {
            foreach ($slides as $slide) {
                $files = $fs->get_area_files($fromcontext->id, 'block_slider', 'slider_slides', $slide->id, 'id ASC', false);
                foreach ($files as $file) {
                    $slide->sliderid = $this->instance->id;
                    $itemid = $DB->insert_record('slider_slides', $slide);
                    $filerecord = ['contextid' => $this->context->id, 'itemid' => $itemid];
                    $fs->create_file_from_storedfile($filerecord, $file);
                }
            }
        }
        return true;
    }
}
