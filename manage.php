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
 * Zarządzanie slajdami: lista, kolejność, widoczność i usuwanie.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_slider\local\manage_helper;
use block_slider\local\slides;
use core\output\notification;

require_once(__DIR__ . '/../../config.php');

$blockid = required_param('blockid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$slideid = optional_param('id', 0, PARAM_INT);
$returnurl = manage_helper::return_url(optional_param('returnurl', '', PARAM_LOCALURL));

$instance = manage_helper::get_instance($blockid);
require_login(manage_helper::get_course($instance));
$manageurl = manage_helper::manage_url($blockid, $returnurl);
$context = manage_helper::setup_page($instance, $manageurl, $returnurl);
$slide = $slideid ? slides::get_slide($slideid, $blockid) : null;

if ($slide && in_array($action, ['move', 'show', 'hide'], true)) {
    require_sesskey();
    if ($action === 'move') {
        slides::move($slide, optional_param('direction', '', PARAM_ALPHA) === 'up' ? 'up' : 'down');
    } else {
        slides::set_visible($slide, $action === 'show');
    }
    redirect($manageurl);
}

if ($slide && $action === 'delete') {
    if (optional_param('confirm', 0, PARAM_BOOL)) {
        require_sesskey();
        slides::delete($slide);
        redirect($manageurl, get_string('slidedeleted', 'block_slider'), null, notification::NOTIFY_SUCCESS);
    }

    $images = slides::get_images($context);
    $message = html_writer::tag('p', get_string('confirmdelete', 'block_slider'));
    $title = format_string((string) $slide->title, true, ['context' => $context]);
    if ($title !== '') {
        $message .= html_writer::tag('p', html_writer::tag('strong', $title));
    }
    if (!empty($images[$slide->id][slides::AREA_IMAGE])) {
        $message .= html_writer::empty_tag('img', [
            'src' => slides::file_url($images[$slide->id][slides::AREA_IMAGE])->out(false),
            'alt' => '',
            'class' => 'block-slider-confirm-image',
        ]);
    }
    $confirmparams = ['action' => 'delete', 'id' => $slide->id, 'confirm' => 1, 'sesskey' => sesskey()];
    $confirmurl = manage_helper::manage_url($blockid, $returnurl, $confirmparams);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('deleteslide', 'block_slider'));
    echo $OUTPUT->confirm($message, $confirmurl, $manageurl);
    echo $OUTPUT->footer();
    die;
}

$renderer = $PAGE->get_renderer('block_slider');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageslides', 'block_slider'));
echo $renderer->render(new \block_slider\output\manage_page($blockid, $context, $returnurl));
echo $OUTPUT->footer();
