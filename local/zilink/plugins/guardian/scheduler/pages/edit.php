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
 * A page for managers to configure parents eves
 *
 * Displays a list of users on the system for selection as teachers for this parent's evening,
 * using {@see parentseve_teacher_form()}, and a form for configuration of date, time and
 * appointment length using {@see parentseve_form}.
 *
 * @author Mike Worth <mike@mike-worth.com>, Mark Johnson <johnsom@tauntons.ac.uk>
 * @copyright Copyright &copy; 2009 Taunton's College
 * @package local_zilink
 * @param id int The ID of an existing parents' evening for editing
 */

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');
require_once(dirname(__FILE__) .'/forms/edit.php');

require_login();
$session = optional_param('session',0, PARAM_INT);
$add = optional_param('add', '', PARAM_TEXT);
$remove = optional_param('remove', '', PARAM_TEXT);

$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey(),'session' => $session);
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/teachers.php', $urlparams);
$PAGE->https_required();
$PAGE->set_url($url, $urlparams);
$PAGE->verify_https_required();

$strmanage = get_string('guardian_scheduler_page_title', 'local_zilink');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);
$PAGE->set_pagelayout('report');

$security = new ZiLinkSecurity();

//$PAGE->requires->css('/local/zilink/plugins/guardian/scheduler/styles.css');
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('guardian_scheduler', 'local_zilink'));

if ($security->IsAllowed('local/zilink:guardian_scheduler_manage')) {
    $PAGE->navbar->add(get_string('guardian_scheduler_session', 'local_zilink'), new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php'));
} else {
    $PAGE->navbar->add(get_string('guardian_scheduler_session', 'local_zilink'));
}

$session = $DB->get_record('zilink_guardian_sched', array('id' => $session));
if (!$session) {
    $PAGE->navbar->add(get_string('create'), new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php'));
} else {
    $PAGE->navbar->add(get_string('edit'));
    $PAGE->navbar->add(date('l jS M Y', $session->timestart));
}

$mform = new guardian_scheduler_form();

if ($session) {
    $formdata = new stdClass;
    $formdata->session = $session->id;
    $formdata->timestart = $session->timestart;
    $formdata->timeend = $session->timeend;
    $formdata->appointmentlength = $session->appointmentlength/60;
    $formdata->info = $session->info;
} else {
    $formdata = new stdClass;
}

$mform->set_data($formdata);

if ($newdata = $mform->get_data()) {
    $newdata->appointmentlength = $newdata->appointmentlength*60;
    unset($newdata->MAX_FILE_SIZE);

    if ($session) {

        // if the evening has been moved to a different day, update any appointments
        // that have already been booked
        if ($session->timestart != $newdata->timestart
            && $session->timeend != $newdata->timeend
            && date('YMd', $session->timestart) == date('YMd', $session->timeend)
            && date('YMd', $newdata->timestart) == date('YMd', $newdata->timeend)
            && date('YMd', $session->timestart) != date('YMd', $newdata->timestart)
            && date('YMd', $session->timeend) != date('YMd', $newdata->timeend)
            ) {

                if ($appointments = $DB->get_records('zilink_guardian_sched_app', array('sessionid' => $session->id))) {
                foreach ($appointments as $appointment) {
                    $time = $appointment->apptime - $session->timestart;
                    $newtime = $newdata->timestart+$time;
                    $DB->set_field('zilink_guardian_sched_app', 'apptime', $newtime, array('id' => $appointment->id));
                }
            }
        }

        $session->timestart = $newdata->timestart;
        $session->timeend = $newdata->timeend;
        $session->appointmentlength = $newdata->appointmentlength;
        $session->info = $newdata->info;
        $DB->update_record('zilink_guardian_sched', $session);
        redirect($CFG->wwwroot.'/local/zilink/plugins/guardian/scheduler/admin/manage.php');
    } else {
        $sessionid = $DB->insert_record('zilink_guardian_sched', $newdata);
        redirect($CFG->wwwroot.'/local/zilink/plugins/guardian/scheduler/admin/manage.php');
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('guardian_scheduler_edit_title', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_edit_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_support_desc', 'local_zilink').html_writer::link('https://schoolsict.zendesk.com/entries/66700116',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $OUTPUT->box(get_string('zilink_plugin_beta', 'local_zilink').get_string('zilink_plugin_support_desc', 'local_zilink') .html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')),array('generalbox','error'));
$mform->display();
echo $OUTPUT->footer();