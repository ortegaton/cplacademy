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

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/core/security.php');
require_once(dirname(__FILE__) . '/forms/book.php');

require_login($SITE);

$courseid = required_param('cid', PARAM_INT);

if (!$courseid == SITEID) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

$urlparams = array('cid' => $courseid, 'sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/student/appointment/book.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('student_appointment', 'local_zilink');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/student/appointment/styles.css');
$tt_url = new moodle_url('/local/zilink/plugins/student/appointment/book.php', $urlparams);
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('student_appointment', 'local_zilink'));
$PAGE->navbar->add(get_string('student_appointment_book', 'local_zilink'), $tt_url);
$PAGE->set_pagelayout('report');

$security = new ZiLinkSecurity();
if ($security->IsAllowed('local/zilink:student_appointment:book'))
{
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, '', 0);
}

$form = new student_appointment_form(null, array('coursecontext' => context_course::instance($courseid)));

if ($data = $form->get_data()) {
    $form->process($data);
    redirect($CFG->wwwroot.'/course/view.php?id='.$courseid, get_string('student_appointments_booking saved','local_zilink'), 0);
}
$content = $form->display();

$header = $OUTPUT->header();
$footer = $OUTPUT->footer();
echo $header.$content.$footer;

