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
 * Displays form for selecting users as teachers for the current parents' evening
 *
 * @package local_zilink
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 **/

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');

require_login();
$appointment = required_param('appointment', PARAM_INT);
$session = required_param('session', PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey(),'session' => $session);
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/pages/cancel.php', $urlparams);
$PAGE->https_required();

$PAGE->set_url($url, $urlparams);
$PAGE->verify_https_required();

$strmanage = get_string('guardian_scheduler_page_title', 'local_zilink');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);
$PAGE->set_pagelayout('report');

$security = new ZiLinkSecurity();

$PAGE->requires->css('/local/zilink/plugins/guardian/scheduler/styles.css');
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('guardian_scheduler', 'local_zilink'));

if ($security->IsAllowed('local/zilink:guardian_scheduler_manage')) {
    $PAGE->navbar->add(get_string('guardian_scheduler_session', 'local_zilink'), new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php'));
} else {
    $PAGE->navbar->add(get_string('guardian_scheduler_session', 'local_zilink'));
}

if (!$session = $DB->get_record('zilink_guardian_sched', array('id' => $session))) {
    redirect(new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/manage.php'),get_string('guardian_scheduler_no_session','local_zilink'),1);
}

if (!$appointment = $DB->get_record('zilink_guardian_sched_app', array('id' => $appointment))) {
    redirect(new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/manage.php'),get_string('guardian_scheduler_no_appointment','local_zilink'),1);
}

$viewall = $security->IsAllowed('local/zilink:guardian_scheduler_viewall');
$isteacher = session_isteacher($USER->id, $session);

if ($viewall || $isteacher) {
    $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', array('session' => $session->id));
    $PAGE->navbar->add(date('l jS M Y', $session->timestart), $url);
} else {
    $PAGE->navbar->add(date('l jS M Y', $session->timestart));
}
$PAGE->navbar->add(get_string('cancel'));

if ($confirm) {
    $DB->delete_records('zilink_guardian_sched_app', array('id' => $appointment->id));
    $redirectparams = array('session' => $session->id, 'sesskey' => sesskey());
    $redirecturl = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', $redirectparams);
    redirect($redirecturl);
} else {

    $a = new stdClass();
    $a->teacher = fullname($DB->get_record('user',array('id' => $appointment->teacherid)));
    $a->time = date('H:i', $appointment->apptime);
    $a->date = date('d/M/Y', $appointment->apptime);

    $content = $OUTPUT->heading(get_string('guardian_scheduler_appointmentcancel', 'local_zilink', $a), 3);
    $confirmparams = array(
        'confirm' => true,
        'session' => $session->id,
        'appointment' => $appointment->id
    );
    
    $confirmurl = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/cancel.php', $confirmparams);
    $confirmbutton = new single_button($confirmurl, get_string('yes'));

    $cancelparams = array(
        'session' => $session->id,
        'sesskey' => sesskey()
    );
    $cancelurl = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', $cancelparams);
    $cancelbutton = new single_button($cancelurl, get_string('no'), 'get');

    $content .= $OUTPUT->confirm(get_string('guardian_scheduler_confirmcancel', 'local_zilink'),
                                 $confirmbutton,
                                 $cancelbutton);
}

echo $OUTPUT->header();
echo $content;
echo $OUTPUT->footer();