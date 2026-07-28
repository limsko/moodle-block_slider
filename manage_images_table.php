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
 * Class manage_images
 */
class manage_images extends table_sql {

    /** @var \context_block Block context for file URLs. */
    protected $blockcontext;

    /** @var int|null Course id for routing. */
    protected $courseid;

    /**
     * manage_images constructor.
     * @param string $uniqueid
     * @param \context_block $context
     * @param int|null $courseid
     * @throws coding_exception
     */
    public function __construct($uniqueid, \context_block $context, ?int $courseid = null) {
        $this->blockcontext = $context;
        $this->courseid = $courseid;
        parent::__construct($uniqueid);

        // Define the list of columns to show.
        $columns = array('slide_order', 'slide_link', 'slide_title', 'slide_desc', 'slide_image', 'manage');
        $this->define_columns($columns);

        // Define the titles of columns to show in header.
        $headers = array(
                get_string('slide_order', 'block_slider'),
                get_string('slide_url', 'block_slider'),
                get_string('slide_title', 'block_slider'),
                get_string('slide_desc', 'block_slider'),
                get_string('slide_image', 'block_slider'),
                get_string('manage_slides', 'block_slider'),
        );
        $this->define_headers($headers);
    }

    /**
     * Column with slide image.
     *
     * @param $values
     * @return string
     */
    public function col_slide_image($values) {
        $url = moodle_url::make_pluginfile_url(
            $this->blockcontext->id,
            'block_slider',
            'slider_slides',
            $values->id,
            '/',
            $values->slide_image
        )->out(false);
        $alt = !empty($values->slide_title)
            ? format_string($values->slide_title, true, ['context' => $this->blockcontext])
            : s($values->slide_image);
        return html_writer::empty_tag('img', array('src' => $url, 'class' => 'img-thumbnail', 'alt' => $alt));
    }

    /**
     * Column with manage buttons.
     *
     * @param $values
     * @return string
     * @throws coding_exception
     * @throws moodle_exception
     */
    public function col_manage($values) {
        $editparams = array('id' => $values->id, 'sliderid' => $values->sliderid);
        if ($this->courseid) {
            $editparams['course'] = $this->courseid;
        }
        $editurl = new moodle_url('/blocks/slider/manage_images.php', $editparams);
        $editbtn = html_writer::link($editurl, get_string('edit'), array('class' => 'btn btn-primary mb-1'));
        $deleteparams = array('id' => $values->id, 'sliderid' => $values->sliderid, 'sesskey' => sesskey());
        if ($this->courseid) {
            $deleteparams['course'] = $this->courseid;
        }
        $deleteurl = new moodle_url('/blocks/slider/delete_image.php', $deleteparams);
        $deletebtn = html_writer::link($deleteurl, get_string('delete'), array('class' => 'btn btn-danger'));
        return "<p>$editbtn</p><p>$deletebtn</p>";
    }
}
