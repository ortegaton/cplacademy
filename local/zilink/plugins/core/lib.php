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
    
require_once($CFG->dirroot . "/cohort/lib.php");

function local_zilink_core_cron ()
{
    global $CFG, $DB;
    
        if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Starting ZiLink Core Cron Job');
        }
        
        $roles = array(
                    'zilink_webservice'=>(object) array(                
                        'name'=>'ZiLink - WebService',
                        'description'=>'This ZiLink administered role contains all the capabilities required by the ZiLink Web Service User',
                        'context' => array(CONTEXT_SYSTEM),
                        'allowed_capabilities' => array('moodle/course:view',
                                                        'moodle/course:update',
                                                        'moodle/course:viewhiddencourses',
                                                        'moodle/course:create',
                                                        'moodle/category:manage',
                                                        'moodle/category:viewhiddencategories',
                                                        'moodle/cohort:manage',
                                                        'moodle/cohort:assign',
                                                        'enrol/zilink:enrol',
                                                        'webservice/rest:use',
                                                        'moodle/webservice:createtoken',
                                                        'moodle/course:visibility',
                                                        'moodle/user:viewalldetails',
                                                        'moodle/course:enrolconfig'
                                                ),
                        'disallowed_capabilities' => array()
                    ),
                    'zilink_guardians'=>(object) array(
                        'name'=>'ZiLink - Guardians',
                        'description'=>'This ZiLink administered role contains all the capabilities that all guardian users will have',
                        'context' => array(CONTEXT_SYSTEM,CONTEXT_USER),
                        'allowed_capabilities' => array('local/zilink:guardian_view',
                                                        'moodle/course:view'),
                        'disallowed_capabilities' => array()
                    ),
                    'zilink_guardians_restricted'=>(object) array(
                        'name'=>'ZiLink - Restricted Guardians',
                        'description'=>'This ZiLink administered role contains all the capabilities that all restricted guardian users will have',
                        'context' => array(CONTEXT_SYSTEM,CONTEXT_USER),
                        'allowed_capabilities' => array('local/zilink:guardian_view',
                                                        'moodle/course:view'),
                        'disallowed_capabilities' => array()
                    ),
                    'zilink_report_writer_manager'=>(object) array(
                        'name'=>'ZiLink - Report Writer - Manager',
                        'description'=>'This ZiLink administered role contains all the capabilities required required to administer ZiLink Student Reporting Reports',
                        'context' => array(CONTEXT_SYSTEM),
                        'allowed_capabilities' => array('local/zilink:report_writer_configure'),
                        'disallowed_capabilities' => array()
                    ),
                    'zilink_report_writer_senior_management_team'=>(object) array(
                        'name'=>'ZiLink - Report Writer - Senior Management Team',
                        'description'=>'This ZiLink administered role contains all the capabilities required to sign off ZiLink Student Reporting Reports',
                        'context' => array(CONTEXT_COURSECAT),
                        'allowed_capabilities' => array('local/zilink:report_writer_senior_management_team_edit'),
                        'disallowed_capabilities' => array()
                    ),
                    'zilink_report_writer_subject_leader'=>(object) array(
                        'name'=>'ZiLink - Report Writer - Subject Leader',
                        'description'=>'This ZiLink administered role contains all the capabilities required to sign off ZiLink Student Reporting Reports',
                        'context' => array(CONTEXT_COURSECAT),
                        'allowed_capabilities' => array('local/zilink:report_writer_subject_leader_edit'),
                        'disallowed_capabilities' => array()
                    ),
                    /*
                    'zilink_roombooking_manager'=>(object) array(
                        'name'=>'ZiLink - Room Booking Manager',
                        'description'=>'This ZiLink administered role contains all the capabilities required to administer ZiLink Room Booking System',
                        'context' => array(CONTEXT_SYSTEM),
                        'allowed_capabilities' => array('block/zilink:roombooking_manageroommaintenance'),
                        'disallowed_capabilities' => array()
                    ),
                    'zilink_roombooking_manager'=>(object) array(
                        'name'=>'ZiLink - Room Booking Manager',
                        'description'=>'This ZiLink administered role contains all the capabilities required to administer ZiLink Room Booking System',
                        'context' => array(CONTEXT_SYSTEM),
                        'allowed_capabilities' => array('block/zilink:roombooking_manageroommaintenance'),
                        'disallowed_capabilities' => array()
                    ),
                     * */
                    'coursecreator'=>(object) array(
                        'name'=>'Course creator',
                        'description'=>'Course creators can create new courses.',
                        'context' => array(CONTEXT_SYSTEM,CONTEXT_COURSECAT),
                        'allowed_capabilities' => array('enrol/zilink:enrol',
                                                        'enrol/zilink_cohort:config'),
                        'disallowed_capabilities' => array()
                    ),
                    'editingteacher'=>(object) array(
                        'name'=>'Teacher',
                        'description'=>'Teachers can do anything within a course, including changing the activities and grading students.',
                        'context' => array(CONTEXT_COURSE,CONTEXT_MODULE),
                        'allowed_capabilities' => array('local/zilink:timetable_viewown',
                                                        'local/zilink:timetable_viewothers'),
                        'disallowed_capabilities' => array()
                    ),
                    'student'=>(object) array(
                        'name'=>'Student',
                        'description'=>'Students generally have fewer privileges within a course.',
                        'context' => array(CONTEXT_COURSE,CONTEXT_MODULE),
                        'allowed_capabilities' => array('local/zilink:timetable_viewown'),
                        'disallowed_capabilities' => array()
                    ),
                );
    /*
    $incorrectrole = $DB->get_record('role',array('shortname' => 'zilink_roombooking_magaer'));
    if(is_object($incorrectrole)) {
        $incorrectrole->shortname = 'zilink_roombooking_manager';
        $DB->update_record('role',$incorrectrole);
    }
       */
    foreach ($roles as $sname => $role)
    {       
        $mdl_role = $DB->get_record('role',array('shortname' => $sname));
        if (!is_object($mdl_role))              
        {
            $id = create_role($role->name, $sname, $role->description);
            $mdl_role = $DB->get_record('role',array('id'=> $id, 'shortname' => $sname));
        }

        if (is_object($mdl_role))  
        {
            foreach($role->context as $context)
            {
                $mdl_context = $DB->get_record('role_context_levels',array('roleid' => $mdl_role->id, 'contextlevel' => $context));
                if(!is_object($mdl_context))
                {
                    $new_conext                 = new stdClass();
                    $new_conext->roleid         = $mdl_role->id;
                    $new_conext->contextlevel   = $context;
                    $DB->insert_record('role_context_levels', $new_conext);
                }
            }
            
            foreach ($role->allowed_capabilities as $capability)
            {
                $cap = $DB->get_record('capabilities', array('name' => $capability));
                if (is_object($cap))
                {
                    $role_cap = $DB->get_record('role_capabilities', array('contextid' => 1, 'roleid' => $mdl_role->id, 'capability' => $capability));
                    if (!is_object($role_cap))
                    {
                        $new_cap                = new stdClass();
                        $new_cap->contextid     = 1;
                        $new_cap->roleid        = $mdl_role->id;
                        $new_cap->capability    = $capability;
                        $new_cap->permission    = 1;
                        $new_cap->timemodified  = time();
                        $new_cap->modifierid    = 2;
    
                        $DB->insert_record('role_capabilities', $new_cap);
                    }
                }
            }
            
            foreach ($role->disallowed_capabilities as $capability)
            {
                $cap = $DB->get_record('capabilities', array('name' => $capability));
                if (is_object($cap))
                {
                    $role_cap = $DB->get_record('role_capabilities', array('contextid' => 1, 'roleid' => $role->id, 'capability' => $capability));
                    if (is_object($role_cap))
                    {
                        $DB->delete_record('role_capabilities', array('id' => $role_cap->id));
                    }
                }
            }
        }
    }

    $teacher_cohort = $DB->get_record('cohort',array('name' => 'Teachers', 'component' => 'enrol_zilink'));
        
    if(!is_object($teacher_cohort))
    {       
        $teacher_cohort                 = new StdClass();
        $teacher_cohort->name           = 'Teachers';
        $teacher_cohort->idnumber       = '00000000000000000000000000000000';
        $teacher_cohort->description    = 'Administered by ZiLink for Moodle';
        $teacher_cohort->contextid      = context_system::instance()->id;
        $teacher_cohort->component      = 'enrol_zilink';
        $teacher_cohort->id = cohort_add_cohort($teacher_cohort);
    }
    
    $all_staff_cohort = $DB->get_record('cohort',array('name' => 'All Staff', 'component' => 'enrol_zilink'));
        
    if(!is_object($all_staff_cohort))
    {       
        $all_staff_cohort                 = new StdClass();
        $all_staff_cohort->name           = 'All Staff';
        $all_staff_cohort->idnumber       = '00000000000000000000000000000001';
        $all_staff_cohort->description    = 'Administered by ZiLink for Moodle';
        $all_staff_cohort->contextid      = context_system::instance()->id;
        $all_staff_cohort->component      = 'enrol_zilink';
        $all_staff_cohort->id = cohort_add_cohort($all_staff_cohort);
    }
    
    $non_teacher_cohort = $DB->get_record('cohort',array('name' => 'Non Teachers', 'component' => 'enrol_zilink'));
        
    if(!is_object($non_teacher_cohort))
    {       
        $non_teacher_cohort                 = new StdClass();
        $non_teacher_cohort->name           = 'Non Teachers';
        $non_teacher_cohort->idnumber       = '00000000000000000000000000000002';
        $non_teacher_cohort->description    = 'Administered by ZiLink for Moodle';
        $non_teacher_cohort->contextid      = context_system::instance()->id;
        $non_teacher_cohort->component      = 'enrol_zilink';
        $non_teacher_cohort->id = cohort_add_cohort($non_teacher_cohort);
    }
    
    $user = $DB->get_record('user', array('username' =>'schoolsict'));
    if(!is_object($user))
    {
        $user               = new stdClass();
        $user->auth         = 'manual';
        $user->email        = 'support@schoolsict.net';
        $user->firstname    = 'SchoolsICT';
        $user->lastname     = 'Support';
        $user->username     = 'schoolsict';
        $user->confirmed    = 1;
        $user->lastip       = getremoteaddr();
        $user->timemodified = time();
        $user->mnethostid   = $CFG->mnet_localhost_id;
        $user->lang         = $CFG->lang;
        $user->password     = generate_password(10);
        
        $user->id = $DB->insert_record('user',$user);
    }
    
    $user = $DB->get_record('user', array('username' =>'zilink'));
    if(!is_object($user))
    {
        $user               = new stdClass();
        $user->auth         = 'manual';
        $user->email        = 'support@schoolsict.net';
        $user->firstname    = 'ZiLink';
        $user->lastname     = 'Webservice User';
        $user->username     = 'zilink';
        $user->confirmed    = 1;
        $user->lastip       = getremoteaddr();
        $user->timemodified = time();
        $user->mnethostid   = $CFG->mnet_localhost_id;
        $user->lang         = $CFG->lang;
        $user->password     = generate_password(10);
        
        $user->id = $DB->insert_record('user',$user);
    }

    if(is_object($user))
    {
        $user->password = generate_password(10);
        {
            $DB->update_record('user',$user);
        }
    }
    
    $role = $DB->get_record('role',array('shortname' => 'zilink_webservice'));
    if(is_object($role))
    {
        $ctx = context_system::instance();
        role_assign($role->id, $user->id, $ctx->id,'', NULL);
    }
    require_once($CFG->dirroot.'/webservice/lib.php');
    
    set_config('enablewebservices', 1);
    if(!isset($CFG->webserviceprotocols)) {
        set_config('webserviceprotocols', 'rest');
    } else {
        $active = explode(',', $CFG->webserviceprotocols);
        if(!in_array('rest', $active))
        {
           set_config('webserviceprotocols', $CFG->webserviceprotocols.',rest');
        }
    }
    
    $webservicemanager = new webservice();
    
    $service = $DB->get_record('external_services',array('name' => 'ZiLink'));
    if(!is_object($service))
    {
        $service = new stdClass();
        $service->name = 'ZiLink';
        $service->enabled = 1;
        $service->restrictedusers = 0;
        $service->timecreated = time();
        
        $service->id = $DB->insert_record('external_services',$service);
    }
    
    $functions = $webservicemanager->get_not_associated_external_functions($service->id); 
    foreach ($functions as $functionid => $function) 
    {
        if (strlen(strstr($function->name,'zilink'))>0) 
        {
            $webservicemanager->add_external_function_to_service($function->name, $service->id);
        }
    }
    
    if(!$DB->record_exists('external_tokens',array('userid' => $user->id, 'externalserviceid' => $service->id)))
    {
        external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service->id,  $user->id, context_system::instance(), null, null);
    }
    //$webservicemanager->generate_user_ws_tokens($user->id);
    
    purge_all_caches();
    
    if((defined('CLI_SCRIPT') && CLI_SCRIPT)  && $CFG->debug == DEBUG_DEVELOPER) {
            mtrace('Finished ZiLink Core Cron Job');
        }
    
}

function zilink_get_all_instances_in_course($modulename, $course, $userid=NULL, $includeinvisible=false) {
    return zilink_get_all_instances_in_courses($modulename, array($course->id => $course), $userid, $includeinvisible);
}

function zilink_get_all_instances_in_courses($modulename, $courses, $userid=NULL, $includeinvisible=false) {
     global $CFG, $DB;
 
      if (!core_component::is_valid_plugin_name('mod', $modulename)) {
         throw new coding_exception('Invalid modulename parameter');
      }
 
      $outputarray = array();
  
      if (empty($courses) || !is_array($courses) || count($courses) == 0) {
          return $outputarray;
      }
  
      list($coursessql, $params) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED, 'c0');
      $params['modulename'] = $modulename;
  
        //cm.groupmembersonly,
      if (!$rawmods = $DB->get_records_sql("SELECT cm.id AS coursemodule, m.*, cw.section, cm.visible AS visible,
                                                   cm.groupmode, cm.groupingid,  cm.availability As availability, cm.added as created, 
                                                   cm.completionexpected as completionexpected
                                              FROM {course_modules} cm, {course_sections} cw, {modules} md,
                                                   {".$modulename."} m
                                             WHERE cm.course $coursessql AND
                                                   cm.instance = m.id AND
                                                   cm.section = cw.id AND
                                                   md.name = :modulename AND
                                                   md.id = cm.module", $params)) {
          return $outputarray;
      }
  
      foreach ($courses as $course) {
          $modinfo = get_fast_modinfo($course, $userid);
  
          if (empty($modinfo->instances[$modulename])) {
              continue;
          }
  
          foreach ($modinfo->instances[$modulename] as $cm) {
              if (!$includeinvisible and !$cm->uservisible) {
                  continue;
              }
              if (!isset($rawmods[$cm->id])) {
                  continue;
              }
              $instance = $rawmods[$cm->id];
              if (!empty($cm->extra)) {
                  $instance->extra = $cm->extra;
              }
              $outputarray[] = $instance;
          }
      }
  
      return $outputarray;
  }
  
  
  
