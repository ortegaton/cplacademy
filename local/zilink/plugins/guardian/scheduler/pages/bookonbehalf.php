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
 * Display the appointment booking form
 *
 * Displays a page containing fields for the student's and parent's name,
 * along with a button to add a new appointment. The button uses an AJAX call to {@see book_ss.php}
 * to display a list of teachers and times for each requested appointment.
 *
 * @package block_parentseve
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009 Taunton's College, Southampton, UK
 * @param int id The ID of the parents' evening
 */
 
 
 
require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/view/interfaces/default/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');
require_once(dirname(__FILE__) .'/forms/bookonbehalf.php');
require_once($CFG->dirroot.'/local/zilink/lib.php');

require_login();
$session = required_param('session', PARAM_INT);
$sesskey = required_param('sesskey',PARAM_RAW);
$offset = optional_param('offset',-1,PARAM_INT);

confirm_sesskey($sesskey);

$context = context_system::instance();
$PAGE->set_context($context);

$urlparams = array('sesskey' => sesskey(),'session' => $session);
$url = new moodle_url($CFG->httpswwwroot.'/local/zilink/plugins/guardian/scheduler/pages/book.php', $urlparams);
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

$session = $DB->get_record('zilink_guardian_sched', array('id' => $session));
if (!$session) {
    redirect($CFG->httpswwwroot.'/course/view.php?id='.SITEID,get_string('requiredpermissionmissing','local_zilink'),1);
} else {
    $PAGE->navbar->add(get_string('edit'));
    $PAGE->navbar->add(date('l jS M Y', $session->timestart));
}
$PAGE->navbar->add(get_string('guardian_scheduler_book', 'local_zilink'));

$now = (isset($CFG->zilink_effective_date) && $CFG->zilink_effective_date > 0) ? $CFG->zilink_effective_date : time();
if ($session->timeend < $now) {
    redirect($CFG->httpswwwroot,get_string('guardian_scheduler_oldparentseve','local_zilink'),1);
}

$jsmodule = array(
    'name'     => 'local_zilink_guardian_scheduler_onbehalf',
    'fullpath' => '/local/zilink/plugins/guardian/scheduler/module.js',
    'requires' => array('base', 'io', 'node', 'json', 'selector-css3'),
    'strings' => array(
        array('guardian_scheduler_teacher', 'local_zilink'),
        array('guardian_scheduler_noappointments', 'local_zilink'),
        array('guardian_scheduler_noparentname', 'local_zilink'),
        array('guardian_scheduler_nostudentname', 'local_zilink'),
        array('guardian_scheduler_noappointmentwith', 'local_zilink'),
        array('guardian_scheduler_mustcorrect', 'local_zilink'),
        array('cancel', 'moodle'),
        array('guardian_scheduler_busy', 'local_zilink'),
        array('guardian_scheduler_selectteacher', 'local_zilink'),
        array('guardian_scheduler_formfailed', 'local_zilink')
    )
);

$PAGE->requires->js_init_call('M.local_zilink_guardian_scheduler_onbehalf.init',
                              array(sesskey()),
                              false,
                              $jsmodule);


$cats = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root));
$subjects = array();
if(!empty($cats))
{
    foreach($cats as $cat)
    {
        $subs = zilinkdeserialise($CFG->zilink_guardian_view_default_subjects_allowed);
        
        if(isset($subs[$cat->id]) && $subs[$cat->id] == 1) {
            $category = $DB->get_record('course_categories',array('id' => $cat->id ));
            $subjects['c-'.$category->id] = $category->name;
        }        
    }
}

$dateTime = new DateTime(); 
$dateTime->setTimezone(new DateTimeZone('UTC'));  

$slots = array();
$start = $session->timestart;
$end = $session->timeend;
$length = $session->appointmentlength;

for ($time = $start; $time < $end; $time += $length) {
    if (empty($appcron[$time])) {
        $dateTime->setTimestamp ($time);    
        $t = ($time);
        $slots['t-'.$t] = $dateTime->format('H:i');
    }
}

$bookings = array();
$params = array(
                'sessionid' => $session->id,
                'teacherid' => $USER->id
            );
            
$booked = $DB->get_records('zilink_guardian_sched_app', $params);

foreach($booked as $booking)
{
    $bookings['t-'.$booking->apptime] = $booking;
}


$form = new guardian_scheduler_booking_onbehalf_form(null,array('sid' => $session, 'subjects' => $subjects, 'slots' => $slots ,'bookings' => $bookings, 'students' => array()));
if($data = $form->get_data())
{

    $times = array();
    foreach ($data->subjects as $time => $subjectid) {
        if($subjectid <> '0') {
            if($data->students[$time] <> '0' &&  $data->guardians[$time] <> '0' ) {
            
                $appointment = new object();
                $appointment->sessionid = $data->session;
                $appointment->teacherid = $USER->id;
                $appointment->apptime = str_replace('t-','',$time);
                $appointment->subjectid = str_replace('c-','',$subjectid);
                $appointment->guardianid = str_replace('g-','',$data->guardians[$time]);
                $appointment->studentid = str_replace('s-','',$data->students[$time]);
                
                $params = array(
                    'sessionid' => $appointment->sessionid,
                    'teacherid' => $appointment->teacherid,
                    'apptime' => $appointment->apptime
                );
                
                if (!$DB->record_exists('zilink_guardian_sched_app', $params) || (isset($times[$appointment->teacherid]) && $times[$appointment->teacherid] ==  $appointment->apptime)) {
                
                    $times[$appointment->teacherid] =  $appointment->apptime;
                    
                    $params = array(
                        'sessionid' => $appointment->sessionid,
                        'teacherid' => $appointment->teacherid,
                        'subjectid' => $appointment->subjectid, 
                        'guardianid' => $appointment->guardianid,
                        'studentid' => $appointment->studentid
                    );
                    if ($DB->record_exists('zilink_guardian_sched_app', $params)) {
                        $DB->delete_records('zilink_guardian_sched_app',$params);
                    }
                    if ($DB->insert_record('zilink_guardian_sched_app', $appointment)) {
                        $successes[] = $appointment;
                    } else {
                        $appointment->teacher = fullname($teacher);
                        $failures[] = $appointment;
                    }
                } else {
                    $appointment->teacher = fullname($teacher);
                    $failures[] = $appointment;
                }
            }
        }
    }

    $urlparams = array('session' => (isset($appointment->sessionid) ? $appointment->sessionid : 0),'sesskey' => sesskey());
    redirect(new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/book.php',$urlparams), get_string('guardian_scheduler_appbooked', 'local_zilink'), 1);

} else {
    
    $apps = $DB->get_records('zilink_guardian_sched_app', array('sessionid' => $session->id, 'guardianid' => $USER->id));
    
    $formdata = new stdClass;
    $formdata->session = $session->id;
    $formdata->offset = $offset;
    $form->set_data($formdata);
    
    echo $OUTPUT->header();
    echo $form->display();
    echo $OUTPUT->footer();
}