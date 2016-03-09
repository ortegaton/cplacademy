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
 * Defines the edit form for the ZiLink block sub plugin timetable_week
 *
 * @package     block_zilink
 * @subpackage     timetable_week
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net) 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../lib/formslib.php');

class block_zilink_timetable_week_edit_form extends moodleform {
    public function definition() {

        global $CFG;

        $mform =& $this->_form;
        $mform->addElement('html', '<h2>'. get_string('timetable_week_title', 'block_zilink') .'<h2>');
        $mform->addElement('filemanager', 'week1_filemanager', get_string('timetable_week_first_week', 'block_zilink'), null,
                    array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('image') ));
        $mform->addElement('filemanager', 'week2_filemanager', get_string('timetable_week_second_week', 'block_zilink'), null,
                    array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('image') ));
        $mform->addElement('filemanager', 'holiday_filemanager', get_string('timetable_week_holiday', 'block_zilink'), null,
                    array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1, 'accepted_types' => array('image') ));

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'context');
        $mform->setType('context', PARAM_INT);
        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->addElement('hidden', 'pid');
        $mform->setType('pid', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addElement('html', '<div style="margin-left:50%">');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->addElement('html', '</div>');
    }

    public function set_data($data) {

        $editoroptions = $this->get_editor_options();
        $context = new stdClass();
        $context->id = $data->context;

        if (!isset($data->config->week1)) {
            $data->config->week1 = null;
        }
        if (!isset($data->config->week2)) {
            $data->config->week2 = null;
        }
        if (!isset($data->config->holiday)) {
            $data->config->holiday = null;
        }

        $data = file_prepare_standard_filemanager($data, 'week1',
                                                $editoroptions,
                                                $context,
                                                'block_zilink',
                                                'content',
                                                $data->config->week1);

        $data = file_prepare_standard_filemanager($data, 'week2',
                                                $editoroptions,
                                                $context,
                                                'block_zilink',
                                                'content',
                                                $data->config->week2);

        $data = file_prepare_standard_filemanager($data, 'holiday',
                                                  $editoroptions,
                                                  $context,
                                                  'block_zilink',
                                                  'content',
                                                  $data->config->holiday);

        unset($data->config);
        parent::set_data($data);
    }

    protected function get_editor_options() {
        $editoroptions = array();
        $editoroptions['component'] = 'block_zilink';
        $editoroptions['filearea'] = 'content';
        $editoroptions['noclean'] = false;
        $editoroptions['subdirs'] = 0;
        $editoroptions['maxfiles'] = 0;
        $editoroptions['maxbytes'] = 0;
        return $editoroptions;
    }
}