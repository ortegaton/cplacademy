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
 * Defines the settings for the ZiLink local
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/local/zilink/plugins/core/data.php');


class zilink_import_tasks_form extends moodleform {

    public function definition() {
        global $CFG, $DB;  
        
        $mform = & $this->_form;
        
        $mform->addElement('header', 'moodle', get_string('import_tasks','local_zilink'));
        
        if(!empty($this->_customdata['activationkey'])) {
            
            $radioarray=array();
            $radioarray[] = $mform->createElement('radio', 'command', '', 'Full Import', 'FullExport');
            $radioarray[] = $mform->createElement('radio', 'command', '', 'Student Import', 'StudentExport');
            $radioarray[] = $mform->createElement('radio', 'command', '', 'Attendance Import', 'AttednanceExport');
            $radioarray[] = $mform->createElement('radio', 'command', '', 'Assessment Import', 'AssessmentImport');
            $radioarray[] = $mform->createElement('radio', 'command', '', 'Staff Import', 'StaffExport');
            $radioarray[] = $mform->createElement('radio', 'command', '', 'Guardian Import', 'GuardianExport');
            
            $mform->addGroup($radioarray, 'radioar', '', array('<br>'), false);
            
            $this->add_action_buttons(false, get_string('import_run_task','local_zilink'));
        }
        else {
            $mform->addElement('html', '<p>Please run the initial import from ZiNET Connect.</p>');
        }
    }

    function display()
    {
        return $this->_form->toHtml();
    }

}
