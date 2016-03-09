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
 * Defines the capabilities for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    defined('MOODLE_INTERNAL') || die();
    
function local_zilink_cohorts_cron ()
{
        
        global $CFG,$DB;
        
        local_zilink_cohorts_SyncCoursesAndCohorts();
        local_zilink_cohorts_SyncTeachersAndCohort();

}

    function local_zilink_cohorts_SyncCoursesAndCohorts()
    {
        global $CFG,$DB;

        require_once($CFG->dirroot.'/lib/enrollib.php');
        require_once($CFG->dirroot . "/course/lib.php");
        require_once($CFG->dirroot . "/enrol/zilink_cohort/locallib.php");
        
        
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Starting ZiLink course and cohort synchronisation');
        }
        
        $enrol = enrol_get_plugin('zilink_cohort');
        $cohorts = $DB->get_records('cohort');
        
        foreach($cohorts as $cohort) {
            
            if(strlen($cohort->idnumber) == 32) {
                $course = $DB->get_record('course',array('idnumber' => $cohort->idnumber));
                if(!is_object($course))
                    continue;
                
                $zilink_enrol = $DB->get_records('enrol', array('courseid' => $course->id, 'roleid' => 5, 'customint1' => $cohort->id));
                $link = false;
                
                if(is_array($zilink_enrol)) {
                    $enrolinstances = enrol_get_instances($course->id,false);
                    $count = 0;
                    $max = count($zilink_enrol) - 1;
                    foreach ($enrolinstances as $courseenrolinstance) {
                        if ($courseenrolinstance->enrol == "zilink_cohort" && $courseenrolinstance->customint1 == $cohort->id) {
                            continue 2;
                        }
                    }
                }
                
                if (empty($zilink_enrol)) { 
                    $enrol->add_instance($course, array('customint1' => $cohort->id, 'roleid' => 5));
                    enrol_zilink_cohort_sync($course->id);
                    mtrace('    * Linked Cohort '. $cohort->name . ' with Course '. $course->shortname .'.');
                }
            }
        }
    
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Finished ZiLink course and cohort synchronisation');
        }
    }

    function local_zilink_cohorts_SyncTeachersAndCohort()
    {
        global $CFG,$DB;
        
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Starting ZiLink teacher cohort synchronisation');
        }    
            
        
        require_once($CFG->dirroot . "/cohort/lib.php");
        require_once($CFG->dirroot . "/group/lib.php");
        /*
        $teacher_cohort = $DB->get_record('cohort',array('name' => 'Teachers', 'component' => 'enrol_zilink'));
        
        if(!is_object($teacher_cohort))
        {       
            $teacher_cohort                 = new StdClass();
            $teacher_cohort->name           = 'Teachers';
            $teacher_cohort->idnumber       = '00000000000000000000000000000000';
            $teacher_cohort->description    = 'Administered by ZiLink for Moodle2';
            $teacher_cohort->contextid      = get_system_context()->id;
            $teacher_cohort->component      = 'enrol_zilink';
            $teacher_cohort->id = cohort_add_cohort($teacher_cohort);
        }
        */
        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        
        $sql = "SELECT ue.userid as id 
                    FROM {user_enrolments} ue, {enrol} e
                    WHERE ue.enrolid = e.id
                    AND e.roleid = :roleid
                    AND e.enrol = 'zilink'
                    GROUP BY ue.userid";
                    
        $enroled_teachers = $DB->get_records_sql($sql,array('roleid' => $role->id));
        $enroled_teacher_ids = array();
        
        foreach ($enroled_teachers as $enroled_teacher)
        {
            $enroled_teacher_ids[] = $enroled_teacher->id;
        }
        
        $sql = "SELECT userid as id 
                FROM {zilink_cohort_teachers} 
                GROUP BY userid";
        
        $known_teachers = $DB->get_records_sql($sql,array(null));
        $known_teacher_ids = array();
        
        foreach ($known_teachers as $known_teacher)
        {
            $known_teacher_ids[] = $known_teacher->id;
        }
        
        if(count($known_teacher_ids) == 0)
        {
            if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
                mtrace('No Teacher Cohort Records Found');
                mtrace('Finished ZiLink teacher cohort synchronisation');
            }    
            return ;
        }
        
        $enrol = enrol_get_plugin('zilink');
        
        if(count($enroled_teacher_ids) == 0) {
        
            //$new_teacher_ids = array_diff($known_teacher_ids,$enroled_teacher_ids);
            $old_teacher_ids = array_diff($enroled_teacher_ids,$known_teacher_ids);
            
            foreach($old_teacher_ids as $old_teacher_id)
            {
                $user = $DB->get_record('user',array('id' => $old_teacher_id, 'deleted' => '0'));
                
                if(!is_object($user))
                {
                    continue;
                }
                
                $sql = "SELECT e.courseid as id 
                        FROM {user_enrolments} ue, {enrol} e
                        WHERE ue.userid = :userid
                        AND ue.enrolid = e.id
                        AND e.roleid = :roleid
                        AND e.enrol = 'zilink'";
                        
                $teacher_courses = $DB->get_records_sql($sql,array('userid' => $user->id,'roleid' => $role->id));   
                $teacher_course_ids = array();
                
                if(count($teacher_courses) > 0)
                {
                    foreach($teacher_courses as $teacher_course)
                    {
                        if(!in_array($teacher_course->id, $teacher_course_ids))
                        {
                            $teacher_course_ids[] = $teacher_course->id;
                        }
                    }
                }
                
                foreach ($teacher_course_ids as $remove_enrolment) 
                {
                    $instance = null;
                    $enrolinstances = enrol_get_instances($remove_enrolment,false);
    
                    foreach ($enrolinstances as $courseenrolinstance) {
                        if ($courseenrolinstance->enrol == "zilink") {
                            $instance = $courseenrolinstance;
                            break;
                        }
                    }
                    if (!empty($instance)) {
        
                        $mdl_course = $DB->get_record('course', array('id' => $remove_enrolment));
                        
                        $enrol->unenrol_user($instance, $user->id);
                    }
                }
                /*
                if($DB->record_exists('cohort_members',array('cohortid' => $teacher_cohort->id, 'userid' => $user->id)))
                {
                    if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
                        mtrace('   * Removing '.fullname($user) .' From Teacher Cohort.');
                    }
                    //cohort_remove_member($teacher_cohort->id, $user->id);
                }
                 * */
            }
        }
        foreach($known_teacher_ids as $known_teacher_id)
        {
            $user = $DB->get_record('user',array('id' => $known_teacher_id, 'deleted' => '0'));
            
            if(!is_object($user))
            {
                continue;
            }
            
            $sql = "SELECT e.courseid as id 
                    FROM {user_enrolments} ue, {enrol} e
                    WHERE ue.userid = :userid
                    AND ue.enrolid = e.id
                    AND e.roleid = :roleid
                    AND e.enrol = 'zilink'";
                    
            $teacher_courses = $DB->get_records_sql($sql,array('userid' => $user->id,'roleid' => $role->id));
            $teacher_course_ids = array();
            
            if(count($teacher_courses) > 0)
            {
                foreach($teacher_courses as $teacher_course)
                {
                    if(!in_array($teacher_course->id, $teacher_course_ids))
                    {
                        $teacher_course_ids[] = $teacher_course->id;
                    }
                }
            }
            
            $teacher_courses_add = array();
            $teacher_courses_current = array();
            
            if($teacher_cohorts = $DB->get_records('zilink_cohort_teachers', array('userid' => $known_teacher_id)))
            {
                foreach($teacher_cohorts as $teacher_cohort)
                {
                    $cohort_courses = $DB->get_records('enrol',array('enrol' => 'zilink_cohort', 'customint1' => $teacher_cohort->cohortid));
                    
                    if(count($cohort_courses) > 0)
                    {
                        foreach($cohort_courses as $cohort_course)
                        {
                            if(!in_array($cohort_course->courseid, $teacher_course_ids ))
                            {
                                $teacher_courses_add[] = $cohort_course->courseid;
                            } 
                            if(in_array($cohort_course->courseid, $teacher_course_ids ))
                            {
                                $teacher_courses_current[] = $cohort_course->courseid;
                            } 
                        }
                    }
                }
            }
            
            foreach ($teacher_courses_add as $course) 
            {
                $mdl_course = $DB->get_record('course', array('id' => $course));
                
                if(is_object($mdl_course))
                {
                    $instance = null;
                    $enrolinstances = enrol_get_instances($mdl_course->id,false);
    
                    foreach ($enrolinstances as $courseenrolinstance) {
                        if ($courseenrolinstance->enrol == "zilink") {
                            $instance = $courseenrolinstance;
                            break;
                        }
                    }
                    if (empty($instance)) {
    
                        $enrol = enrol_get_plugin('zilink');
                        if ($id = $enrol->add_instance($mdl_course, array('customint1' => $mdl_course->id, 'roleid' => $role->id))) {
                            $instance = $DB->get_record('enrol', array('id' => $id), '*', MUST_EXIST);
                            unset($id);
                        } else {
                            $errorparams = new stdClass();
                            $errorparams->courseid = $mdl_course->id;
                            throw new moodle_exception('wsnoinstance', 'enrol_manual', $errorparams);
                        }
                    }
    
                    $enrolment['timestart'] = isset($enrolment['timestart']) ? $enrolment['timestart'] : 0;
                    $enrolment['timeend'] = isset($enrolment['timeend']) ? $enrolment['timeend'] : 0;
                    $enrolment['status'] = (isset($enrolment['suspend']) && !empty($enrolment['suspend'])) ? ENROL_USER_SUSPENDED : ENROL_USER_ACTIVE;
                    
                    $enrol->enrol_user($instance, $user->id, $role->id, $enrolment['timestart'], $enrolment['timeend'], $enrolment['status']);
                }
            } 

            $remove_enrolments = array_diff($teacher_course_ids,$teacher_courses_add,$teacher_courses_current);
           
            foreach ($remove_enrolments as $remove_enrolment) 
            {
                $mdl_course = $DB->get_record('course', array('id' => $remove_enrolment));
                
                if(is_object($mdl_course))
                {
                    $instance = null;
                    $enrolinstances = enrol_get_instances($mdl_course->id,false);
    
                    foreach ($enrolinstances as $courseenrolinstance) {
                        if ($courseenrolinstance->enrol == "zilink") {
                            $instance = $courseenrolinstance;
                            break;
                        }
                    }
                    if (!empty($instance)) {
                        
                        $enrol->unenrol_user($instance, $user->id);
                    }
                }
            }  
        }
        
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Finished ZiLink teacher cohort synchronisation');
            mtrace('Starting ZiLink teacher groups synchronisation');
        }
        
        $teachers = $DB->get_records('zilink_cohort_teachers');
        
        foreach($teachers as $teacher) {
         
            $user = $DB->get_record('user',array('id' => $teacher->userid, 'deleted' => '0'));
            
            
            if(!is_object($user))
            {
                continue;
            }
            
            $cohort = $DB->get_record('cohort',array('id' => $teacher->cohortid));

            if(!is_object($cohort))
                continue;
            
            $course = $DB->get_record('course',array('idnumber' => $cohort->idnumber));

            if(is_object($course))
            {
                $groups = $DB->get_records('groups',array('courseid' => $course->id, 'name' => $cohort->name ));
            
                if(!empty($group))
                {
                      
                    foreach($groups as $group)
                    {
                        if(!is_object($group))
                            continue;
                    
                        if(!groups_is_member($group->id, $teacher->userid)) {
                                groups_add_member($group->id, $teacher->userid);
                                $user = $DB->get_record('user',array('id' => $teacher->userid));
                                mtrace('   * Adding '.fullname($user) .' to group ' . $group->name . ' in course '. $course->shortname);
                        }
                    }
                }
            } else {
            
                $courses = enrol_get_users_courses($teacher->userid);
                
                foreach($courses as $course) {
                    
                    $group = $DB->get_record('groups',array('courseid' => $course->id, 'name' => $cohort->name ));
                    
                    if(!is_object($group))
                        continue;
        
                    if(!groups_is_member($group->id, $teacher->userid)) {
                        groups_add_member($group->id, $teacher->userid);
                        $user = $DB->get_record('user',array('id' => $teacher->userid));
                        mtrace('   * Adding '.fullname($user) .' to group ' . $group->name . ' in course '. $course->shortname);
                    }


                }
            }
        }
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Finished ZiLink teacher groups synchronisation');
        }
    }
