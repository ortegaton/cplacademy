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
 * A page to display schedules for a parents eve to teachers and managers,
 * and allow anyone else to make appontments.
 *
 * @package block_parentseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 * @param int $id The ID of the parent's evening
 **/

require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');

require_login();
$session = required_param('session', PARAM_INT);
$justmyschedule = optional_param('my', 0, PARAM_BOOL);
$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('session' => $session, 'sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/guardian/scheduler/pages/scheduler/view.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('guardian_scheduler_page_title', 'local_zilink');

$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/guardian/scheduler/styles.css');
$url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/schedule.php', $urlparams);
$PAGE->navbar->add(get_string('pluginname_short','local_zilink'));
$PAGE->navbar->add(get_string('guardian', 'local_zilink'), $url);
$PAGE->navbar->add(get_string('guardian_scheduler', 'local_zilink'), $url);
$PAGE->set_pagelayout('report');


if (!$session = $DB->get_record('zilink_guardian_sched', array('id' => $session))) {
    print_error('guardian_scheduler_noparentseve', 'local_zilink');
}

$security = new ZiLinkSecurity();

/*
if(!$security->IsAllowed('local/zilink:guardian_scheduler_manage') || $session->timeend < time()
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
}
*/

$output = $PAGE->get_renderer('local_zilink');

$content = $output->booking_link($session->id, $session);

$is_teacher = session_isteacher($USER->id, $session);
$cancancel = $security->IsAllowed('local/zilink:guardian_scheduler_cancel');

if ($security->IsAllowed('local/zilink:guardian_scheduler_viewall') || $is_teacher) {

    if ($justmyschedule) {

         // Show link to user's own schedule
        $content .= $output->allschedules_link($session->id, $session);
        $schedule = session_get_schedule($USER, $session, $session);
        $content .= $output->schedule_table($session->id, $session, $schedule, $cancancel);
    } else {
        if ($is_teacher) {
            // Show link to user's own schedule
            $content .= $output->myschedule_link($session->id ,$session);
        }

        //show all teachers' schedules
        $teachers = session_get_teachers($session);
        foreach ($teachers as $teacher) {
            $schedule = session_get_schedule($teacher, $session, $session->id);
            $headingtext = get_string('guardian_scheduler_schedulefor', 'local_zilink', fullname($teacher));
            $content .= $OUTPUT->heading($headingtext, 3, 'guardian_scheduler_schedule_header');
            $content .= $output->schedule_table($session->id, $session, $schedule, $cancancel);
        }
    }

} else {
    print_error('nopermissions');
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('guardian_scheduler_schedule_title', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_schedule_title_desc', 'local_zilink'));
echo $OUTPUT->box(get_string('guardian_scheduler_support_desc', 'local_zilink').html_writer::link('http://support.zilink.co.uk/hc/',get_string('support_site','local_zilink'),array('target'=> '_blank')));
echo $content;
echo $OUTPUT->footer();