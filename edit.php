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
 * Dodawanie i edycja slajdu.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2015-2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use block_slider\form\slide_form;
use block_slider\local\manage_helper;
use block_slider\local\slides;
use core\output\notification;

require_once(__DIR__ . '/../../config.php');

$blockid = required_param('blockid', PARAM_INT);
$slideid = optional_param('id', 0, PARAM_INT);
$returnparam = optional_param('returnurl', '', PARAM_LOCALURL);
$returnurl = manage_helper::return_url($returnparam);

$instance = manage_helper::get_instance($blockid);
require_login(manage_helper::get_course($instance));
$url = new moodle_url('/blocks/slider/edit.php', ['blockid' => $blockid, 'id' => $slideid, 'returnurl' => $returnparam]);
$context = manage_helper::setup_page($instance, $url, $returnurl);
$manageurl = manage_helper::manage_url($blockid, $returnurl);
$slide = $slideid ? slides::get_slide($slideid, $blockid) : null;

$pagetitle = get_string($slide ? 'editslide' : 'addslide', 'block_slider');
$PAGE->navbar->add($pagetitle);
$PAGE->set_title($pagetitle);

$form = new slide_form($url, ['editing' => (bool) $slide]);

if ($form->is_cancelled()) {
    redirect($manageurl);
}

if ($data = $form->get_data()) {
    $slide = $slide ? slides::update($slide, $data) : slides::create($blockid, $data);
    $options = slides::file_options();
    file_save_draft_area_files($data->image, $context->id, 'block_slider', slides::AREA_IMAGE, $slide->id, $options);
    file_save_draft_area_files($data->mobileimage, $context->id, 'block_slider', slides::AREA_MOBILE, $slide->id, $options);
    redirect($manageurl, get_string('slidesaved', 'block_slider'), null, notification::NOTIFY_SUCCESS);
}

$formdata = $slide ? clone($slide) : (object) ['visible' => 1];
$formdata->blockid = $blockid;
$formdata->id = $slide->id ?? 0;
$formdata->returnurl = $returnparam;
foreach (['image' => slides::AREA_IMAGE, 'mobileimage' => slides::AREA_MOBILE] as $field => $area) {
    $draftid = file_get_submitted_draft_itemid($field);
    file_prepare_draft_area($draftid, $context->id, 'block_slider', $area, $slide->id ?? null, slides::file_options());
    $formdata->$field = $draftid;
}
$form->set_data($formdata);

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);
$form->display();
echo $OUTPUT->footer();
