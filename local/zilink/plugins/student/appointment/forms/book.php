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
 * Processes the appointment form
 *
 * This page recieves the form from the course appointments block, passess the data
 * to the validation method, and if all's well passess it on the the process method
 * for insertion into the database.  It then returns the user to the page displaying the block.
 * If any errors were generated during validation, processing is skipped and the errors
 * are stored in the session for display in the block.
 *
 * @package block_course_appointments
 * @author      Mark Johnson <mark.johnson@tauntons.ac.uk>
 * @copyright   2010 Tauntons College, UK
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/bennu/bennu.inc.php');

class student_appointment_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        
        $coursecontext = $this->_customdata['coursecontext'];
        $cap = 'local/zilink:student_appointment_bookable';
        $students = get_users_by_capability($coursecontext, $cap, '', 'lastname, firstname');
        
        $studentlist = array();
        foreach ($students as $student) {
            $studentlist[$student->id] = fullname($student);
        }
         
        $mform->addElement('hidden', 'courseid', $coursecontext->instanceid);
        $mform->setType('courseid',PARAM_INT);

        $mform->addElement('header', 'moodle', get_string('student_appointment_booking_form', 'local_zilink'));
        
        if(empty($studentlist)) {
            $mform->addElement('static','student',get_string('student_appointment_student', 'local_zilink'),'No Students Avaliable');
        } else {
            $mform->addElement('select',
                           'student',
                           get_string('student_appointment_student', 'local_zilink'),
                           $studentlist);
            $mform->addRule('student', null, 'required', null, 'client');
        } 
        

        $years = array('startyear' => date('Y'), 'stopyear' => date('Y')+1, 'optional' => false);
        $mform->addElement('date_time_selector',
                           'date',
                           get_string('student_appointment_datetime', 'local_zilink'),
                           $years);
        $mform->addRule('date', null, 'required', null, 'client');
        $mform->addElement('checkbox',
                           'notify',
                           get_string('student_appointment_notifystudent', 'local_zilink'));
        $mform->addElement('submit', 'book', get_string('student_appointment_book', 'local_zilink'));

    }

    public function validate($data) {
        if (empty($data['date']) || $data['date'] == -1) {
            $errors['date'] = get_string('student_appointment_invaliddate', 'local_zilink');
        } else if ($data['date'] < time()) {
            $errors['date'] = get_string('student_appointment_pastdate', 'local_zilink');
        }
        return $errors;
    }

    /**
     * Generate the HTML for the form, capture it in an output buffer, then return it
     *
     * @return string
     */
    public function display() {
        //finalize the form definition if not yet done
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        ob_start();
        $this->_form->display();
        $form = ob_get_clean();

        return $form;
    }

    public function process($data) {
        global $USER, $COURSE, $CFG, $DB;
        global $sms;
        $student = $DB->get_record('user', array('id' => $data->student));
        $names = new stdClass;
        $names->student = fullname($student);
        $names->teacher = fullname($USER);
        $uuid = Bennu::generate_guid();
        $appointment = new stdClass;
        $appointment->name = get_string('student_appointment_entryname', 'local_zilink', $names->student);
        $appointment->description = get_string('student_appointment_entrydescription',
                                               'local_zilink',
                                               $names);
        $appointment->userid = $USER->id;
        $appointment->timestart = $data->date;

        // To identify the two appointments as linked, we use the same UUID for both, but replace
        // the dashes with T (for Teacher) and S (For student). Since neither character is
        // Hexadecimal, they wont occur in any generated UUID.
        $appointment->uuid = str_replace('-', 'T', $uuid);
        $appointment->format = 1;
        $DB->insert_record('event', $appointment);
        $appointment->name = get_string('student_appointment_entryname', 'local_zilink', $names->teacher);
        $appointment->userid = $data->student;
        $appointment->uuid = str_replace('-', 'S', $uuid);
        $DB->insert_record('event', $appointment);
        $names->date = date('d/m/Y', $data->date);
        $names->time = date('H:i', $data->date);
        $notified = false;
        if ($data->notify) {
            $subject = get_string('student_appointment_notifysubject', 'local_zilink', $names->teacher);
            $message = get_string('student_appointment_notifytext', 'local_zilink', $names);
            $notified = email_to_user($student, $USER, $subject, $message);
        }
        $SESSION->course_appointments = array();
        if ($data->notify && !$notified) {
            $SESSION->course_appointments['errors'][] = get_string('student_appointment_notnotified',
                                                                   'local_zilink');
        }
    }
}