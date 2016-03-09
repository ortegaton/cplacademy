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
 * Defines the renderer for the Parents' Eve block
 *
 * Defines {@see local_zilink_renderer}
 *
 * @package local_zilink
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 */

/**
 * Renderer for Parents' Evening block
 */
class local_zilink_guardian_scheduler_renderer extends plugin_renderer_base {

    /**
     * Displays user selectors for adding teachers to the current parents' evening
     *
     * @param $potential parentseve_teacher_selector
     * @param $selected parentseve_teacher_selector
     * @return string HTML form containing the selectors
     */
    public function teacher_selector ($potential, $selected) {

        $output = '';
        $table = new html_table('teacher_selector');
        $row = new html_table_row();
        $row->cells[] = $selected->display(true);
        $addattrs = array(
            'class' => 'add_button',
            'name' => 'add',
            'type' => 'submit',
            'value' => $this->output->larrow().' '.get_string('add')
        );
        $cell = html_writer::empty_tag('input', $addattrs);
        $delattrs = array(
            'class' => 'remove_button',
            'name' => 'remove',
            'type' => 'submit',
            'value' => get_string('remove').' '.$this->output->rarrow()
        );
        $cell .= html_writer::empty_tag('input', $delattrs);
        $row->cells[] = $cell;
        $row->cells[] = $potential->display(true);
        $table->data[] = $row;

        $formattrs = array('action' => $this->page->url->out(false), 'method' => 'post');
        $output = html_writer::start_tag('form', $formattrs);
        $output .= html_writer::table($table);
        $output .= html_writer::end_tag('form');

        return $output;
    }

    /**
     * A table of booked appointments, optionally with links to cancel
     *
     * @param $id int instance ID, for cancel links
     * @param $session object The parents's evening record
     * @param $appointments array The appointments to be displayed
     * @param $cancel bool Display cancel links?
     * @return string HTML table containing the booked appointments
     */
    public function schedule_table($id, $session, $appointments = array(), $cancel = false) {

        global $DB,$OUTPUT;
        
        $output = '';
$cancel = true;
        $table = new html_table();
        $table->head = array(get_string('guardian_scheduler_apptime', 'local_zilink'),
                        get_string('subject', 'local_zilink'),
                        get_string('guardian_scheduler_parentname', 'local_zilink'),
                        get_string('guardian_scheduler_studentname', 'local_zilink'),
                        get_string('action'));
        if ($cancel) {
            $table->head[] = '';
        }

        $appcron = array();
        if (!empty($appointments)) {
            foreach ($appointments as $appointment) {
                
                $appcron[$appointment->apptime]['subject'] = $DB->get_record('course_categories', array('id' => $appointment->subjectid))->name;
                $appcron[$appointment->apptime]['parentname'] = fullname($DB->get_record('user', array('id' => $appointment->guardianid)));
                $appcron[$appointment->apptime]['studentname'] = fullname($DB->get_record('user', array('id' => $appointment->studentid)));
                $appcron[$appointment->apptime]['id'] = $appointment->id;
                $appcron[$appointment->apptime]['sessionid'] = $appointment->sessionid;
            }
        } 

        $start = $session->timestart;
        $end = $session->timeend;
        $length = $session->appointmentlength;
        date_default_timezone_set('UTC');
        for ($time = $start; $time < $end; $time += $length) {

            $row = array();
            $row[] = date('G:i', $time);
            $row[] = '';
            $row[] = '';
            $row[] = '';
            $row[] = '';

            if (isset($appcron[$time]) && !empty($appcron[$time])) {
                $row[1] = $appcron[$time]['subject'];
                $row[2] = $appcron[$time]['parentname'];
                $row[3] = $appcron[$time]['studentname'];
                if ($cancel) {
                    $cancelparams = array('session' => $appcron[$time]['sessionid'],'appointment' => $appcron[$time]['id'], 'sesskey' => sesskey());
                    $cancelurl = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/cancel.php', $cancelparams);
                    $row[4] = html_writer::link($cancelurl, $OUTPUT->pix_icon("t/delete", get_string('delete')));
                }
                
            }

            $table->data[] = $row;
        }

        $output .= html_writer::table($table);

        return $output;
    }

    /**
     * A link to the booking form
     *
     * @param $id int The instance ID
     * @param $session object The Parents' Evening record
     * @return string HTML link
     */
    public function booking_link($id, $session) {
        global $OUTPUT;
        
        $bookparams = array('session' => $session->id, 'sesskey' => sesskey());
        $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/bookonbehalf.php', $bookparams);
        return $OUTPUT->single_button($url,get_string('guardian_scheduler_bookapps', 'local_zilink'));
        //return html_writer::link($url, get_string('guardian_scheduler_bookapps', 'local_zilink'));
    }

    /**
     * A link to display all schedules
     *
     * @param $id int The instance ID
     * @param $session object The Parents' Evening record
     * @return string HTML link
     */
    public function allschedules_link($id, $session) {
        global $OUTPUT;
        
        $bookparams = array('session' => $session->id, 'sesskey' => sesskey());
        $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', $allparams);
        return $OUTPUT->single_button($url,get_string('guardian_scheduler_allschedules', 'local_zilink'));
       
    }

    /**
     * A link to display just the current user's schedule
     *
     * @param $id int The instance ID
     * @param $session object The Parents' Evening record
     * @return string HTML link
     */
    public function myschedule_link($id, $session) {
        global $OUTPUT;
        $myparams = array('id' => $id, 'session' => $session->id, 'my' => 1, 'sesskey' => sesskey());
        $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', $myparams);
        //return html_writer::link($url, get_string('guardian_scheduler_justmyschedule', 'local_zilink'));
        return $OUTPUT->single_button($url,get_string('guardian_scheduler_justmyschedule', 'local_zilink'));
    }

    /**
     * Additional information about the parents' evening
     *
     * @param $starttime int The start time for the Parents' Evening
     * @param $info string The information to be displayed
     * @return string HTML paragraph containing the info
     */
    public function booking_info($starttime, $info) {
        
        global $OUTPUT;
        
        $formatteddate = (object)array('date' => date('l jS F Y', $starttime));
        $output = get_string('guardian_scheduler_on', 'local_zilink', $formatteddate);
        $output .= html_writer::tag('p', $info, array('class' => 'info'));
        return $output;
    }

    /**
     * The skeleton booking form, to be filled out by AJAX when the newapp button is clicked
     *
     * @param $url moodle_url Action URL for the form
     * @return string HTML form containing skeleton for the booking form
     */
    public function booking_form($url) {
        $output = '';
        $formattrs = array(
            'method' => 'post',
            'action' => $url->out(false),
            'id' => 'parentseve_form'
        );
        $output .= html_writer::start_tag('form', $formattrs);

        $names = html_writer::label(get_string('guardian_scheduler_parentname', 'local_zilink'), 'parentname');
        $names .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'parentname'));
        $names .= html_writer::label(get_string('guardian_scheduler_studentname', 'local_zilink'), 'studentname');
        $names .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'studentname'));

        $output .= $this->output->container($names, 'names');
        $buttonattrs = array('type' => 'button', 'id' => 'newapp_button');
        $inputattrs = array(
            'type' => 'submit',
            'value' => get_string('guardian_scheduler_confirmapps', 'local_zilink')
        );
        $strnewapp = get_string('guardian_scheduler_newapp', 'local_zilink');
        $buttons = html_writer::tag('button', $strnewapp, $buttonattrs);
        $buttons .= html_writer::empty_tag('input', $inputattrs);
        $output .= $this->output->container($buttons, 'parentseve_buttons');
        $placeholder = '<!--AJAX will put the schedules in here-->';
        $output .= $this->output->container($placeholder, '', 'parentseve_appointments');
        $output .= $this->output->container('', '', 'clearfix');
        $output .= html_writer::end_tag('form');

        return $output;
    }

    /**
     * Notification of success/failure creating bookings
     *
     * @param $successes array Appointments successfully booked
     * @param $failures array Appointments that couldn't be booked
     * @param $url moodle_url URL to link to
     * @return string HTML paragraphs containing notifications
     */
    public function booking_response($successes, $failures, $url) {
        $output = '';
        $items = array();
        foreach ($successes as $success) {
            $args = (object)array(
                'teacher' => $success->teacher,
                'apptime' => date('G:i', $success->apptime)
            );
            $items[] = get_string('guardian_scheduler_appbooked', 'local_zilink', $args);
        }
        foreach ($failures as $failure) {
            $args = (object)array(
                'teacher' => $failure->teacher,
                'apptime' => date('G:i', $failure->apptime)
            );
            $items[] = get_string('guardian_scheduler_appnotbooked', 'local_zilink', $args);
        }
        $output .= html_writer::alist($items);
        $success = get_string('guardian_scheduler_success', 'local_zilink', count($successes));
        $output .= html_writer::tag('p', $success);

        if (count($failures)) {
            $fail = get_string('guardian_scheduler_fail', 'local_zilink', count($failures));
            $output .= html_writer::tag('p', $fail);
        }

        $output .= $this->output->heading(get_string('guardian_scheduler_printsave', 'local_zilink'), 4);
        $output .= html_writer::link($url, get_string('guardian_scheduler_backtoappointments', 'local_zilink'));

        return $output;

    }

    /**
     * Warning to IE<8 users that the booking form wont work
     *
     * @param $altmethod string Alternative method of booking appointments
     * @return string HTML div containing the warning and alternative method
     */
    public function ie_warning($altmethod) {
        $strwarning = get_string('guardian_scheduler_iewarning', 'local_zilink');
        $stralt = get_string('guardian_scheduler_iealternatively', 'local_zilink').$altmethod;

        return $this->output->box($strwarning.' '.$stralt, 'generalbox iewarning');
    }
}