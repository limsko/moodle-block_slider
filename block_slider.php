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
    public function init() {
        $this->title = get_string('pluginname', 'block_slider');
    }

    /**
     * @return stdClass|stdObject
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function get_content() {
        global $CFG, $DB;
        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            $this->content->text = '';
        }

        $this->content->text .= '<div class="slider"><div id="slides'.$this->instance->id.'" style="display: none;">';

        // Get and display images.
        if ($slides = $DB->get_records('slider_slides', array('sliderid' => $this->instance->id), 'slide_order ASC')) {
            foreach ($slides as $slide) {

                $imageurl = $CFG->wwwroot . '/pluginfile.php/' . $this->context->id . '/block_slider/slider_slides/' . $slide->id .
                        '/' . $slide->slide_image;
                if (!empty($slide->slide_link)) {
                    $this->content->text .= html_writer::start_tag('a', array('href' => $slide->slide_link, 'rel' => 'nofollow'));
                }
                $this->content->text .= html_writer::empty_tag('img',
                        array('src' => $imageurl, 'class' => 'img', 'alt' => $slide->slide_image));
                if (!empty($slide->slide_link)) {
                    $this->content->text .= html_writer::end_tag('a');
                }
            }
        }

        // Navigation Left/Right.
        if (!empty($this->config->navigation)) {
            $this->content->text .= '<a href="#" class="slidesjs-previous slidesjs-navigation">
    <i class="icon fa fa-chevron-left icon-large" aria-hidden="true" aria-label="Prev"></i></a>';
            $this->content->text .= '<a href="#" class="slidesjs-next slidesjs-navigation">
    <i class="icon fa fa-chevron-right icon-large" aria-hidden="true" aria-label="Next"></i></a>';
        }

        $this->content->text .= '</div></div>';

        if (!empty($this->config->width) and is_numeric($this->config->width)) {
            $width = $this->config->width;
        } else {
            $width = 940;
        }

        if (!empty($this->config->height) and is_numeric($this->config->height)) {
            $height = $this->config->height;
        } else {
            $height = 528;
        }

        if (!empty($this->config->interval) and is_numeric($this->config->interval)) {
            $interval = $this->config->interval;
        } else {
            $interval = 5000;
        }

        if (!empty($this->config->effect)) {
            $effect = $this->config->effect;
        } else {
            $effect = 'fade';
        }

        if (!empty($this->config->pagination)) {
            $pag = true;
        } else {
            $pag = false;
        }

        if (!empty($this->config->autoplay)) {
            $autoplay = true;
        } else {
            $autoplay = false;
        }

        $nav = false;

        $this->page->requires->js_call_amd('block_slider/slides', 'init',
                array($width, $height, $effect, $interval, $autoplay, $pag, $nav, $this->instance->id));

        // If user has capability of editing, add button.
        if (has_capability('block/slider:manage', $this->context)) {
            $editurl = new moodle_url('/blocks/slider/manage_images.php', array('sliderid' => $this->instance->id));
            $this->content->footer = html_writer::tag('a', get_string('manage_slides', 'block_slider'),
                    array('href' => $editurl, 'class' => 'btn btn-primary'));
        }

        return $this->content;
    }

    /**
     * @return bool
     */
    public function has_config() {
        return false;
    }

    /**
     * @return bool
     */
    public function instance_allow_multiple() {
        return true;
    }

    /**
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
     * @return bool
     */
    public function hide_header() {
        global $PAGE;
        if ($PAGE->user_is_editing()) {
            return false;
        } else {
            return true;
        }
    }
}