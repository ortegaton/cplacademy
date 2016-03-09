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
 * AJAX script to return the availablibilty of a teacher
 *
 * Accepts a teacher ID and a parents evening ID, and returns a JSON
 * array of appointments, indicating whether the teacher is avilable
 * or booked for each slot.
 *
 * @package local_zilink
 * @author Mark Johnson <johnsom@tauntons.ac.uk>, Mike Worth <mike@mike-worth.com>
 * @copyright Copyright &copy; 2009, Taunton's College, Southampton, UK
 **/

define(AJAX_SCRIPT, true);
require_once(dirname(__FILE__) . '/../../../../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');
require_once(dirname(dirname(__FILE__)).'/renderer.php');
require_once(dirname(__FILE__) .'/forms/edit.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);


$action = optional_param('action','teachertimes',PARAM_RAW);

if ($action == 'teachertimes') {
    $session = required_param('session', PARAM_INT); 
    $teacherid = str_replace('tid-', '', required_param('teacher', PARAM_TEXT));
    $guardianid = required_param('guardian', PARAM_INT); 
    $studentid = required_param('student', PARAM_INT);
    $subjectid = required_param('subject', PARAM_INT);
    
    if (!$session = $DB->get_record('zilink_guardian_sched', array('id' => $session))) {
        header('HTTP/1.1 404 Not Found');
        die(get_string('parentsevenotfound', 'local_zilink'));
    }
    
    if ($session->appointmentlength == 0) {
        header('HTTP/1.1 500 Internal Server Error');
        die(get_string('appointmentlengthzero', 'local_zilink'));
    }
    
    if($teacherid == 0)
    {
        $slot = new stdClass;
        $slot->displaytime = get_string('guardian_scheduler_select_teacher','local_zilink');
        $slot->time = '0';
        $slots[] = $slot;
        echo json_encode((object)array('slots' => $slots));
        die();
    }
    // In order to avoid a loop of DB calls, fetch all the relevant appointments then put them
    // into an array which php can manipulate a lot quicker
    $appcron = array();
    $params = array('teacherid' => $teacherid, 'sessionid' => $session->id);
    if ($appointments = $DB->get_records('zilink_guardian_sched_app', $params, '', 'id, apptime')) {
        foreach ($appointments as $appointment) {
            $appcron[$appointment->apptime]=true;
        }
    }
    
    $params = array('teacherid' => $teacherid, 'sessionid' => $session->id, 'guardianid' =>$guardianid, 'studentid' => $studentid, 'subjectid' => $subjectid);
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
            $slot = new stdClass;
            $slot->displaytime = date('G:i', $time);
            $slot->time = 't-'.$time;
            $slots[] = $slot;
        }
    }
    
    echo json_encode((object)array('slots' => $slots));
} else if ( $action == 'guardians') {
    
    $studentid = substr(required_param('student', PARAM_RAW),2);

    $guardians = array();
    $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'), '*', MUST_EXIST);
                $guardianrestictedrole = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'), '*', MUST_EXIST);
    
    $sql = 'SELECT c.instanceid, c.instanceid, g.id, g.idnumber, g.firstname, g.lastname '.
                       'FROM {role_assignments} ra, '.
                       '     {context} c, '.
                       '     {user} u, '.
                       '     {user} g '.
                       'WHERE u.id = '.$studentid .' '.
                       'AND   ra.roleid IN ('.$guardianrole->id.','.$guardianrestictedrole->id.') '.
                       'AND   ra.contextid = c.id '.
                       'AND   c.instanceid = u.id '.
                       'AND   ra.userid = g.id '.
                       'AND   c.contextlevel = ' . CONTEXT_USER;
    
                $studentguardians = $DB->get_records_sql($sql, null);
    
    if($studentguardians) {
        
        $guardian = new stdClass();
        $guardian->id = 0;
        $guardian->name = get_string('guardian_scheduler_select_guardian','local_zilink');
        $guardians[] = $guardian;
        
        foreach ($studentguardians as $g) {
            $user = $DB->get_record('user',array('id' => $g->id));
            $guardian = new stdClass();
            $guardian->id = $g->id;
            $guardian->name = fullname($user);
            $guardians[] = $guardian;
        }
    } else {
        $guardian = new stdClass();
        $guardian->id = 0;
        $guardian->name = get_string('guardian_scheduler_no_guardians_found','local_zilink');
        $guardians[] = $guardian;
    }

    header('Content-Type: application/json');
    echo json_encode($guardians);    
} else if ( $action == 'students') {

    $subjectid = substr(required_param('subject', PARAM_RAW),2);
    $students = array();
    
    $courses = enrol_get_users_courses($USER->id);
    foreach($courses as $course) {
    
        $mdl_category = $DB->get_record('course_categories',array('id' => $course->category));
        
        if (isset($mdl_category->ctxpath)) {
            $categories = explode('/',$mdl_category->ctxpath);
        } else {
            $categories = explode('/',$mdl_category->path);
        }

        if (in_array($subjectid,$categories)) {
        
            $enrolinstances = enrol_get_instances($course->id,true);
            $enrolments = $DB->get_records('enrol',array('courseid' => $course->id, 'roleid' => 5, 'enrol'=> 'zilink_cohort' ));
            foreach ($enrolments as $enrolment) {
                $members = $DB->get_records('cohort_members',array('cohortid' =>$enrolment->customint1));
                if($members) {
                    foreach($members as $member) {
                        if(!in_array($member->userid,$students)) {
                            
                            if(empty($students)) {
                                $student = new stdClass;
                                $student->id = '0';
                                $student->name = get_string('guardian_scheduler_select_student','local_zilink');
                                $students[] = $student;
                            }
                            $student = new stdClass;
                            $student->id = 's-'.$member->userid;
                            $user = $DB->get_record('user',array('id' => $member->userid));
                            $student->name = fullname($user);
                            $students[preg_replace('/[^a-z0-9]+\Z/i', '', $user->lastname.$user->firstname)] = $student;
                        }
                    }
                }
            }
        }
    }

    if(empty($students)) {
        $student = new stdClass;
        $student->id = '0';
        $student->name = get_string('guardian_scheduler_no_students_found','local_zilink');
        $students[] = $student;
    }

    ksort($students);
    header('Content-Type: application/json');
    echo json_encode($students);  
} 

class zilink_guardian_scheduler_simple_xml_extended extends SimpleXMLElement
{
    public function Attribute($name) {
        foreach ($this->Attributes() as $key => $val) {
            if ($key == $name) {
                   return (string)$val;
            }
        }
        return '';
    }
}