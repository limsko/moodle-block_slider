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

require_once('../../config.php');

require_login();
require_sesskey();

global $SITE;

require_once($CFG->libdir . '/tablelib.php');
require_once('manage_images_table.php');
require_once('lib.php');

$sliderid = required_param('sliderid', PARAM_INT);
$id = required_param('id', PARAM_INT);
$confirm = optional_param('confirm', null, PARAM_BOOL);
$courseid = optional_param('course', null, PARAM_INT);

if ($courseid && ($course = get_course($courseid))) {
    $PAGE->set_course($course);
}

$params = array('view' => 'manage', 'sliderid' => $sliderid);
if ($courseid) {
    $params['course'] = $courseid;
}
$redirecturl = new moodle_url('/blocks/slider/manage_images.php', $params);

$baseparams = array('view' => 'manage', 'sliderid' => $sliderid, 'id' => $id);
if ($courseid) {
    $baseparams['course'] = $courseid;
}
$baseurl = new moodle_url('/blocks/slider/delete_image.php', $baseparams);

$confirmparams = $baseparams;
$confirmparams['confirm'] = 1;
$confirmparams['sesskey'] = sesskey();
$confirmurl = new moodle_url('/blocks/slider/delete_image.php', $confirmparams);

$PAGE->navbar->add(get_string('manage_slides', 'block_slider'), $redirecturl);
$PAGE->navbar->add(get_string('delete'));

$context = context_block::instance($sliderid);
block_slider_require_manage($context);

$PAGE->set_context($context);
$PAGE->set_url($baseurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('manage_slides', 'block_slider'));
if (!empty($PAGE->course->id)) {
    $PAGE->set_heading(format_string($PAGE->course->fullname));
} else {
    $PAGE->set_heading(format_string($SITE->fullname));
}

if (!$slide = $DB->get_record('slider_slides', array('id' => $id))) {
    redirect($redirecturl, get_string('errorinvalidslide', 'block_slider'), null,
            \core\output\notification::NOTIFY_ERROR);
} else {
    if (!$confirm) {
        echo $OUTPUT->header();
        $confirm = html_writer::tag('h3', get_string('confirm_deletion', 'block_slider'));
        $confirm .= html_writer::tag('a', get_string('confirm'), array('class' => 'btn btn-primary', 'href' => $confirmurl)) . ' ';
        $confirm .= html_writer::tag('a', get_string('cancel'), array('class' => 'btn btn-secondary', 'href' => $redirecturl));
        echo html_writer::tag('div', $confirm, array('class' => 'box'));
        echo $OUTPUT->footer();
    } else if ($confirm) {
        confirm_sesskey();
        block_slider_delete_slide($slide);
        redirect($redirecturl, get_string('deleted', 'block_slider'), null,
                \core\output\notification::NOTIFY_SUCCESS);
    }
}
