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
require_once(dirname(__FILE__) .'/forms/book.php');

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

try {
    $guardian_view = new ZiLinkGuardianView();
    $count = 0;
    if(count($guardian_view->people['children']) > 0)
    {
        $children = array();
        
        foreach($guardian_view->people['children'] as $idnumber => $child)
        {
            $children[$count] = fullname($child->user);
            $count++;
        }
    }

    if($count > 1 && $offset == -1)
    {
        $params = array('session' => $session->id,
                        'sesskey' => sesskey());
        $url = new moodle_url('/local/zilink/plugins/guardian/scheduler/pages/select_child.php',$params);
        redirect($url,'',1);
    }
    
    if ($session->timeend < $guardian_view->geteffectivedate()) {
        redirect($CFG->httpswwwroot,get_string('oldparentseve','local_zilink'),1);
    }
        
} catch (Exception $e) {
    redirect($CFG->httpswwwroot.'/course/view.php?id='.SITEID,$e->getMessage(),1);
}





$jsmodule = array(
    'name'     => 'local_zilink_guardian_scheduler',
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

$PAGE->requires->js_init_call('M.local_zilink_guardian_scheduler.init',
                              array($session->id,$USER->id,$guardian_view->child->user->id,sesskey()),
                              false,
                              $jsmodule);


    
$subjects = $guardian_view->GetPublishedSubjects();

$sessions = array();


$courses = enrol_get_users_courses($guardian_view->child->user->id);

foreach ($subjects as $id => $subject) {
    
    $cohorts = array();
    
    foreach($courses as $course) {
    
        $mdl_category = $DB->get_record('course_categories',array('id' => $course->category));
        
        if (isset($mdl_category->ctxpath)) {
            $categories = explode('/',$mdl_category->ctxpath);
        } else {
            $categories = explode('/',$mdl_category->path);
        }
        
        
        if (in_array($id,$categories)) {
        
            $enrolinstances = enrol_get_instances($course->id,true);   
            $enrolments = $DB->get_records('enrol',array('courseid' => $course->id, 'roleid' => 5, 'enrol'=> 'zilink_cohort' ));
            
            foreach ($enrolments as $enrolment) {
                if($DB->record_exists('cohort_members',array('userid' => $guardian_view->child->user->id,'cohortid' =>$enrolment->customint1))) {
                    if(!in_array($enrolment->customint1,$cohorts)) {
                        $cohorts[] = $enrolment->customint1;
                    }
                }
            }
        }
    }

    foreach ($cohorts as $cohort) {
            
        $teachers = $DB->get_records('zilink_cohort_teachers',array('cohortid' => $cohort));
        $appcron = array();
        
        foreach ($teachers as $teacher) {

           if(session_isteacher($teacher->userid,$session)) {
                $params = array('teacherid' => $teacher->userid, 'sessionid' => $session->id);
                if ($appointments = $DB->get_records('zilink_guardian_sched_app', $params, '', 'id, apptime')) {
                    foreach ($appointments as $appointment) {
                        $appcron[$appointment->apptime] = true;
                    }
                }
                
                $params = array('teacherid' => $teacher->userid, 'sessionid' => $session->id, 'guardianid' => $USER->id, 'studentid' => $guardian_view->child->user->id, 'subjectid' => $id);
                if ($appointments = $DB->get_records('zilink_guardian_sched_app', $params, '', 'id, apptime')) {
                    foreach ($appointments as $appointment) {
                        unset($appcron[$appointment->apptime]);
                    }
                }
                
                $slots = array();
                $start = $session->timestart;
                $end = $session->timeend;
                $length = $session->appointmentlength;
                date_default_timezone_set('UTC');
                for ($time = $start; $time < $end; $time += $length) {
                    if (empty($appcron[$time])) {
                        
                        $t = ($time);
                        $slots['t-'.$t] = date('G:i', $time);
                    }
                }
                $sessions[$id]['tid-'.$teacher->userid] = $slots;
            }
        }
    }

}

$bookings = array();
$params = array(
                'sessionid' => $session->id,
                'guardianid' => $USER->id,
                'studentid' => $guardian_view->child->user->id
            );
$booked = $DB->get_records('zilink_guardian_sched_app', $params);

foreach($booked as $booking)
{
    $bookings[$booking->subjectid]['tid-'.$booking->teacherid] = 't-'.$booking->apptime;
}



$form = new guardian_scheduler_booking_form(null,array('sid' => $session, 'sessions' => $sessions ,'bookings' => $bookings));
if($data = $form->get_data())
{
    $times = array();
    foreach ($data->teachers as $subject => $teacherid) {
        if($teacherid <> '0') {
            if($data->times[$subject] <> '0') {
            
                $appointment = new object();
                $appointment->sessionid = $data->session;
                $appointment->teacherid = str_replace('tid-','',$teacherid);
                $appointment->apptime = str_replace('t-','',$data->times[$subject]);
                $appointment->subjectid = $subject;
                $appointment->guardianid = $USER->id;
                $appointment->studentid = $guardian_view->child->user->id;
                
                $params = array(
                    'sessionid' => $appointment->sessionid,
                    'teacherid' => $appointment->teacherid,
                    'apptime' => $appointment->apptime
                );
                    
                if(isset($data->delete_appointment[$subject])) {
                    
                    if($DB->record_exists('zilink_guardian_sched_app', $params)) {
                     
                        $DB->delete_records('zilink_guardian_sched_app', $params);
                    }
                } else {
                    
                    
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
            } else {
                
            }
        } 
    }

    $urlparams = array('sesskey' => sesskey());
    redirect(new moodle_url('/'), get_string('guardian_scheduler_appupdated', 'local_zilink'), 1);
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