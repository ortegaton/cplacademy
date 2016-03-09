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
 * Defines forms for {@see edit.php}
 *
 * Defines {@see parentseve_form} and {@see parenteseve_teacher_form()} for displaying
 * forms in edit.php, used for creating and editing of parents' evenings.
 *
 * @package local_zilink
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 */

require_once($CFG->libdir.'/formslib.php');

/**
 * Defines the configuration form
 *
 */
class guardian_scheduler_form extends moodleform {

    /**
     * Defines the form elements
     */
    public function definition() {
        global $DB;
        $mform    =& $this->_form;
        $mform->addElement('header',
                           'parentseveheader',
                           get_string('guardian_scheduler_createnew', 'local_zilink'));

        $mform->addElement('date_time_selector',
                           'timestart',
                           get_string('guardian_scheduler_timestart', 'local_zilink'));
        $mform->setType('timestart',PARAM_INT);
        $mform->addElement('date_time_selector',
                           'timeend',
                           get_string('guardian_scheduler_timeend', 'local_zilink'));
        $mform->setType('timeend',PARAM_INT);
        $mform->addElement('text',
                           'appointmentlength',
                           get_string('guardian_scheduler_appointmentlength', 'local_zilink'));
        $mform->setType('appointmentlength',PARAM_INT);
        $mform->addElement('htmleditor',
                           'info',
                           get_string('guardian_scheduler_info', 'local_zilink'),
                           'rows="10" cols="25"');
        $mform->addElement('hidden', 'session');
        $mform->setType('session',PARAM_INT);
        /*
        if (get_config('local_progressreview', 'version')) {
            $sessions = $DB->get_records_menu('progressreview_session', array(), 'deadline_tutor DESC');
            $sessions = array(get_string('choosedots')) + $sessions;
            $mform->addElement('select',
                               'importteachers',
                               get_string('guardian_scheduler_importteachers', 'local_zilink'),
                               $sessions);
        }
        */
        $this->add_action_buttons(false);
    }

    public function definition_after_data() {
        $mform = $this->_form;
        //$mform->disabledIf('importteachers', 'parentseve', 'neq', '');
    }
}