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
$session = required_param('session', PARAM_INT);
$add = optional_param('add', '', PARAM_TEXT);
$remove = optional_param('remove', '', PARAM_TEXT);

$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey(),'session' => $session);
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/pages/teachers.php', $urlparams);
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

$viewall = $security->IsAllowed('local/zilink:guardian_scheduler_viewall');
$isteacher = session_isteacher($USER->id, $session);

if ($viewall || $isteacher) {
    $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', array('session' => $session->id));
    $PAGE->navbar->add(date('l jS M Y', $session->timestart), $url);
} else {
    $PAGE->navbar->add(date('l jS M Y', $session->timestart));
}

//if(!$security->IsAllowed('local/zilink:guardian_scheduler_manage'))
//{
//    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
//}

$output = $PAGE->get_renderer('local_zilink');

$potential_selector = new session_teacher_selector('potential_selector', $session);
$selected_selector = new session_selected_teacher_selector('selected_selector', $session);

if (!empty($add)) {
    $newteachers = $potential_selector->get_selected_users();
    foreach ($newteachers as $id => $newteacher) {
        $teacher = new stdClass;
        $teacher->sessionid = $session->id;
        $teacher->userid = $id;
        $teacher->id = $DB->insert_record('zilink_guardian_sched_tch', $teacher);
    }
}

if (!empty($remove)) {
    $oldteachers = $selected_selector->get_selected_users();
    foreach ($oldteachers as $id => $oldteacher) {
        $teacherparams = array('sessionid' => $session->id, 'userid' => $id);
        if ($teacher = $DB->get_record('zilink_guardian_sched_tch', $teacherparams)) {
            $DB->delete_records('zilink_guardian_sched_tch', array('id' => $teacher->id));
        }
    }
}

$content = $output->teacher_selector($potential_selector, $selected_selector);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('guardian_scheduler_teachers_title', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_teachers_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $OUTPUT->box(get_string('zilink_plugin_beta', 'local_zilink').get_string('zilink_plugin_support_desc', 'local_zilink') .html_writer::link('http://support.zilink.co.uk/hc/en-us/articles/200914139',get_string('support_site','local_zilink'),array('target'=> '_blank')),array('generalbox','error'));
echo $content;
echo $OUTPUT->single_button(new moodle_url('/local/zilink/plugins/guardian/scheduler/admin/manage.php'), get_string('back'));
echo $OUTPUT->footer();