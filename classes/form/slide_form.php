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

namespace block_slider\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

use block_slider\local\slides;

/**
 * Formularz dodawania i edycji slajdu.
 *
 * @package     block_slider
 * @author      Kamil Łuczak <kamil@limsko.pl>
 * @copyright   (C) 2026 Kamil Łuczak (https://limsko.pl)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class slide_form extends \moodleform {
    /**
     * Pola formularza.
     */
    protected function definition() {
        $mform = $this->_form;
        $editing = !empty($this->_customdata['editing']);

        $mform->addElement('hidden', 'blockid');
        $mform->setType('blockid', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);

        // Grafika.
        $mform->addElement('header', 'imageheader', get_string('image', 'block_slider'));
        $mform->setExpanded('imageheader');

        $mform->addElement('filemanager', 'image', get_string('image', 'block_slider'), null, slides::file_options());
        $mform->addRule('image', null, 'required', null, 'client');
        $mform->addHelpButton('image', 'image', 'block_slider');

        $mform->addElement('filemanager', 'mobileimage', get_string('mobileimage', 'block_slider'), null, slides::file_options());
        $mform->addHelpButton('mobileimage', 'mobileimage', 'block_slider');

        $mform->addElement('text', 'alttext', get_string('alttext', 'block_slider'), ['size' => 60]);
        $mform->setType('alttext', PARAM_TEXT);
        $mform->addRule('alttext', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('alttext', 'alttext', 'block_slider');

        // Treść.
        $mform->addElement('header', 'contentheader', get_string('content', 'block_slider'));
        $mform->setExpanded('contentheader');

        $mform->addElement('text', 'title', get_string('slidetitle', 'block_slider'), ['size' => 60]);
        $mform->setType('title', PARAM_TEXT);
        $mform->addRule('title', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'subtitle', get_string('subtitle', 'block_slider'), ['size' => 60]);
        $mform->setType('subtitle', PARAM_TEXT);
        $mform->addRule('subtitle', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('textarea', 'description', get_string('description', 'block_slider'), ['rows' => 3, 'cols' => 60]);
        $mform->setType('description', PARAM_TEXT);

        // Link.
        $mform->addElement('header', 'linkheader', get_string('link', 'block_slider'));
        $mform->setExpanded('linkheader');

        $mform->addElement('text', 'link', get_string('link', 'block_slider'), ['size' => 60]);
        $mform->setType('link', PARAM_RAW_TRIMMED);
        $mform->addRule('link', get_string('maximumchars', '', 1333), 'maxlength', 1333, 'client');
        $mform->addHelpButton('link', 'link', 'block_slider');

        $mform->addElement(
            'advcheckbox',
            'newwindow',
            get_string('newwindow', 'block_slider'),
            get_string('newwindow_desc', 'block_slider')
        );

        $mform->addElement('text', 'buttontext', get_string('buttontext', 'block_slider'), ['size' => 30]);
        $mform->setType('buttontext', PARAM_TEXT);
        $mform->addRule('buttontext', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('buttontext', 'buttontext', 'block_slider');

        // Widoczność.
        $mform->addElement('header', 'visibilityheader', get_string('visibility', 'block_slider'));
        $mform->addElement('advcheckbox', 'visible', get_string('visible'), get_string('visible_desc', 'block_slider'));
        $mform->setDefault('visible', 1);

        $this->add_action_buttons(true, $editing ? get_string('savechanges') : get_string('addslide', 'block_slider'));
    }

    /**
     * Walidacja linku i obecności grafiki.
     *
     * @param array $data dane formularza
     * @param array $files pliki
     * @return array błędy
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!slides::is_valid_link((string) ($data['link'] ?? ''))) {
            $errors['link'] = get_string('error_link', 'block_slider');
        }

        $draft = file_get_drafarea_files($data['image'] ?? 0);
        if (empty($draft->list)) {
            $errors['image'] = get_string('required');
        }

        return $errors;
    }
}
