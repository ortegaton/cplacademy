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
 * @package     block_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class ZiLinkSecurity {
    
    public function GetLinkedPeople($person_type,$context = null)
    {
        global $DB;
        
        if($person_type == 'students') {
            
            $role = $DB->get_record('role', array('shortname' => 'student'));
            return get_role_users($role->id, $context);
            
        } if ($person_type =='child' || $person_type == 'children' ) {
        
            return $this->GetLinkedChildren();
        }
        else 
        {
            return array();
        }
    }
    
    public function GetLinkedChildren($idnumber = null)
    {
        //TODO: Handle zilink_guardians_restricted
        
        global $DB,$USER;

                
        $guardian_role = $DB->get_record('role',array('shortname' => 'zilink_guardians'),'*',MUST_EXIST );
        $guardian_resticted_role = $DB->get_record('role',array('shortname' => 'zilink_guardians_restricted'),'*',MUST_EXIST );
        
        if($idnumber) {
            
            $user = $DB->get_record('user', array('idnumber' => $idnumber));
            
            if($user == null) {
                return array();
            }
            $sql = "SELECT  u.id, c.instanceid, u.idnumber, u.firstname, u.lastname
                                    FROM {role_assignments} ra,
                                         {context} c,
                                         {user} u
                                    WHERE ra.userid = $user->id
                                    AND   ra.roleid IN ($guardian_role->id,$guardian_resticted_role->id)
                                    AND   ra.contextid = c.id
                                    AND   c.instanceid = u.id
                                    AND   c.contextlevel = ".CONTEXT_USER ." 
                                    GROUP BY u.id, c.instanceid";
        } else {
            $sql = "SELECT  u.id, c.instanceid, u.idnumber, u.firstname, u.lastname
                                    FROM {role_assignments} ra,
                                         {context} c,
                                         {user} u
                                    WHERE ra.userid = $USER->id
                                    AND   ra.roleid IN ($guardian_role->id,$guardian_resticted_role->id)
                                    AND   ra.contextid = c.id
                                    AND   c.instanceid = u.id
                                    AND   c.contextlevel = ".CONTEXT_USER ." 
                                    GROUP BY u.id, c.instanceid";
        }
     
        $people =  $DB->get_records_sql($sql,null);

        $children = array();
        foreach($people as $child)
        {
            $children[] = $DB->get_record('user',array('id' => $child->id));
        }
        
        return $children;
    }
    
    public function Connected($studentidnumber, $guardianidnumber)
    {
        global $DB;
        
        $guardianrole = $DB->get_record('role', array('shortname' => 'zilink_guardians'), '*', MUST_EXIST );
        $guardianrestictedrole = $DB->get_record('role', array('shortname' => 'zilink_guardians_restricted'), '*', MUST_EXIST );
        
        $sql = 'SELECT c.instanceid as id, u.idnumber as studentidnumber, g.idnumber as guardianidnumber '.
                'FROM '.
                '    {role_assignments} ra, '.
                '    {context} c, '.
                '    {user} u, '.
                '    {user} g '.
                'WHERE '.
                '      ra.roleid IN ( '.$guardianrole->id .','. $guardianrestictedrole->id .') '.
                'AND   ra.contextid = c.id '.
                'AND   c.instanceid = u.id '.
                'AND   ra.userid = g.id '.
                'AND   c.contextlevel = '.CONTEXT_USER .
                ' AND   u.idnumber = :stud '.
                'AND   g.idnumber = :guard ';

        return $DB->record_exists_sql($sql, array('stud' => $studentidnumber, 'guard' => $guardianidnumber));
       
    }

    public function IsAllowed($requirement)
    {
        global $CFG,$USER,$DB;
        
        $courses =  enrol_get_users_courses($USER->id);
                                                                        
        $course_capabilities = array('local/zilink:timetable_viewown',
                                     'local/zilink:bookings_rooms_viewown',
                                     'moodle/course:update',
                                     'block/zilink:poll_manage',
                                     'local/zilink:class_view',
                                     'block/zilink:picture_view',
                                     'local/zilink:timetable_viewothers',
                                     'local/zilink:bookings_rooms_maintenance_manage',
                                     'local/zilink:bookings_rooms_viewalternative',
                                     'moodle/course:viewhiddencourses',
                                     'moodle/category:viewhiddencategories',
                                     'local/zilink:report_writer_view',
                                     'local/zilink:report_writer_subject_teacher_edit',
                                     'local/zilink:report_writer_subject_leader_edit',
                                     'local/zilink:report_writer_senior_management_team_edit',
                                     'local/zilink:homework_report_view',
                                     'local/zilink:homework_report_subject_teacher',
                                     'local/zilink:homework_report_subject_leader',
                                     'local/zilink:homework_report_senior_management_team',
                                     'local/zilink:student_appointment_bookable',
                                     'local/zilink:student_appointment_book',
                                     'local/zilink:guardian_scheduler_book',
                                     'local/zilink:student_view');
        
        if(in_array($requirement, $course_capabilities) || strstr($requirement,'_addinstance'))
        {
              
            foreach($courses as $course)
            {
                
                if(has_capability($requirement, context_course::instance($course->id),$USER))
                    return true;
            }

            if(is_siteadmin($USER->id))
                return true; 
        }
        
        $category_capabilities = array(   'local/zilink:bookings_rooms_maintenance_manage',
                                        'local/zilink:report_writer_configure',
                                        'local/zilink:report_writer_subject_teacher_edit',
                                        'local/zilink:report_writer_subject_leader_edit',
                                        'local/zilink:report_writer_senior_management_team_edit',
                                        'local/zilink:homework_report_subject_teacher',
                                     'local/zilink:homework_report_subject_leader',
                                     'local/zilink:homework_report_senior_management_team');
        
        if(in_array($requirement, $category_capabilities))
        {
            require_once($CFG->dirroot . "/course/lib.php");
            require_once($CFG->libdir . '/coursecatlib.php');
            
            $cat = coursecat::get($CFG->zilink_category_root);
            $categories = $cat->get_children(array('recursive' => 1));

            foreach($categories as $category)
            {
                $context = context_coursecat::instance($category->id);
                if(has_capability($requirement, $context,$USER))
                {
                    return true;
                }
                
                if(is_siteadmin($USER->id))
                {
                    return true;
                }
            }
        }
                                                   
        $system_capabilities = array(   'local/zilink:bookings_rooms_maintenance_manage',
                                        'local/zilink:report_writer_configure',
                                        'local/zilink:report_writer_subject_teacher_edit',
                                        'local/zilink:report_writer_subject_leader_edit',
                                        'local/zilink:report_writer_senior_management_team_edit',
                                        'local/zilink:homework_report_subject_teacher',
                                     'local/zilink:homework_report_subject_leader',
                                     'local/zilink:homework_report_senior_management_team',);
        
        if(in_array($requirement, $system_capabilities))
        {
            
            $context = context_system::instance();
            if(has_capability($requirement, $context,$USER))
            {
                return true;
            }
            
            if(is_siteadmin($USER->id))
            {
                return true;
            }
            
        }
        
        $system_capabilities = array(   'moodle/site:config',
                                        'moodle/course:viewhiddencourses',
                                        'moodle/category:viewhiddencategories',
                                        'local/zilink:guardian_scheduler_manage',
                                        'local/zilink:guardian_scheduler_viewall');
        
        if(in_array($requirement, $system_capabilities))
        {
            $context = context_system::instance();
            if(has_capability($requirement, $context,$USER))
                return true;

            
            if(!empty($USER->realuser))
            {

                if($CFG->zilink_tools_permissions_override == '1')
                {
                    if(has_capability($requirement, $context,$DB->get_record('user',array('id' => $USER->realuser))))
                        return true;
                }
            }
            
            if(is_siteadmin($USER->id))
                return true;

            return false;
        }
        
                                                                                                                 
        $user_capabilities = array( 'local/zilink:guardian_view',
                                    'local/zilink:guardian_view_student_details_photo',
                                    'local/zilink:guardian_view_student_details_attendance',
                                    'local/zilink:guardian_view_student_details_behaviour',
                                    'local/zilink:guardian_view_attendance_recent',
                                    'local/zilink:guardian_view_attendance_overview',
                                    'local/zilink:guardian_view_icons',
                                    'local/zilink:guardian_view_subjects',
                                    'local/zilink:guardian_view_subjects_overview_assessment',
                                    'local/zilink:guardian_view_subjects_assessment',
                                    'local/zilink:guardian_view_subjects_homework',
                                    'local/zilink:guardian_view_subjects_teacher_details',
                                    'local/zilink:guardian_view_subjects_teacher_details_email',
                                    'local/zilink:guardian_view_subjects_submitted_work',
                                    'local/zilink:guardian_view_subjects_reports',
                                    'local/zilink:guardian_view_homework',
                                    'local/zilink:guardian_view_reports',
                                    'local/zilink:guardian_view_timetable',
                                    'local/zilink:guardian_view_information',
                                    'local/zilink:guardian_view_recent_student_photo',
                                    'local/zilink:guardian_scheduler_book');
                                    
        if(in_array($requirement, $user_capabilities))
        {
            $role = $DB->get_record('role',array('shortname' => 'zilink_guardians'),'*',MUST_EXIST );
            $sql = "SELECT u.id, c.instanceid, u.id, u.idnumber, u.firstname, u.lastname
                                         FROM {role_assignments} ra,
                                              {context} c,
                                              {user} u
                                         WHERE ra.userid = $USER->id
                                         AND   ra.roleid = $role->id
                                         AND   ra.contextid = c.id
                                         AND   c.instanceid = u.id
                                         AND   c.contextlevel = ".CONTEXT_USER ."
                                         GROUP BY u.id, c.instanceid";
                                         
            $students = $DB->get_records_sql($sql,null);                        
            if($students)
            {
                foreach($students as $student)
                {
                    if(has_capability($requirement, context_user::instance($student->id),$USER))
                    {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function GetUsersCourses() 
    {
        global $DB, $USER;
    
        // Guest account does not have any courses
        if (isguestuser($USER) || !isloggedin()) 
        {
            return(array());
        }
    
        $basefields = array('id', 'category', 'sortorder',
                            'shortname', 'fullname', 'idnumber',
                            'startdate', 'visible',
                            'groupmode', 'groupmodeforce');
    
        if (empty($fields)) 
        {
            $fields = $basefields;
        } 
        else if (is_string($fields)) 
        {
            // turn the fields from a string to an array
            $fields = explode(',', $fields);
            $fields = array_map('trim', $fields);
            $fields = array_unique(array_merge($basefields, $fields));
        } 
        else if (is_array($fields)) 
        {
            $fields = array_unique(array_merge($basefields, $fields));
        } 
        else 
        {
            throw new coding_exception('Invalid $fileds parameter in enrol_get_my_courses()');
        }
        if (in_array('*', $fields)) 
        {
            $fields = array('*');
        }
    
        $orderby = "";
        $sort    = trim($sort);
        if (!empty($sort)) 
        {
            $rawsorts = explode(',', $sort);
            $sorts = array();
            foreach ($rawsorts as $rawsort) {
                $rawsort = trim($rawsort);
                if (strpos($rawsort, 'c.') === 0) 
                {
                    $rawsort = substr($rawsort, 2);
                }
                $sorts[] = trim($rawsort);
            }
            $sort = 'c.'.implode(',c.', $sorts);
            $orderby = "ORDER BY $sort";
        }
    
        $wheres = array("c.id <> :siteid");
        $params = array('siteid'=>SITEID);
    
        if (isset($USER->loginascontext) and $USER->loginascontext->contextlevel == CONTEXT_COURSE) 
        {
            // list _only_ this course - anything else is asking for trouble...
            $wheres[] = "courseid = :loginas";
            $params['loginas'] = $USER->loginascontext->instanceid;
        }
    
        $coursefields = 'c.' .join(',c.', $fields);
        list($ccselect, $ccjoin) = context_instance_preload_sql('c.id', CONTEXT_COURSE, 'ctx');
        $wheres = implode(" AND ", $wheres);
    
        //note: we can not use DISTINCT + text fields due to Oracle and MS limitations, that is why we have the subselect there
        $sql = "SELECT $coursefields $ccselect
                  FROM {course} c
                  JOIN (SELECT DISTINCT e.courseid
                          FROM {enrol} e
                          JOIN {user_enrolments} ue ON (ue.enrolid = e.id AND ue.userid = :userid)
                         WHERE ue.status = :active AND e.status = :enabled AND ue.timestart < :now1 AND (ue.timeend = 0 OR ue.timeend > :now2)
                       ) en ON (en.courseid = c.id)
               $ccjoin
                 WHERE $wheres
              $orderby";
        $params['userid']  = $USER->id;
        $params['active']  = ENROL_USER_ACTIVE;
        $params['enabled'] = ENROL_INSTANCE_ENABLED;
        $params['now1']    = round(time(), -2); // improves db caching
        $params['now2']    = $params['now1'];
    
        $courses = $DB->get_records_sql($sql, $params, 0, $limit);
    
        // preload contexts and check visibility
        foreach ($courses as $id=>$course) 
        {
            context_instance_preload($course);
            if (!$course->visible) 
            {
                if (!$context = context_course::instance( $id)) 
                {
                    unset($courses[$id]);
                    continue;
                }
            }
            $courses[$id] = $course;
        }
    
        //wow! Is that really all? :-D
    
        return $courses;
    }    
    
    public function GenerateUsername($firstname, $lastname) {
        global $CFG,$DB;

        $username = '';
        $username = strtolower($CFG->zilink_guardian_accounts_username_prefix.substr($firstname, 0, 1).$lastname);

        $username = str_replace('\'', '', $username);
        $username = str_replace(' ', '_', $username);
        $possusername = $username;
        $count = 1;
        while ($DB->record_exists('user', array('username' => $possusername))) {
            $count++;
            $possusername = $username.$count;
        }
        $username = $possusername;
        return strtolower($username);
    }
    
}