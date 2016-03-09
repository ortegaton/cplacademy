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

define('ZILINK_STUDENT_REPORTING_OPEN', 1);
define('ZILINK_STUDENT_REPORTING_PUBLISHED', 2);
define('ZILINK_STUDENT_REPORTING_NOTCOLLECTED', '1');
define('ZILINK_STUDENT_REPORTING_INHERITED', '0');
define('ZILINK_STUDENT_REPORTING_FREETEXT','freetext');

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/data.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/person.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/base.php');
 
class ZiLinkHomeworkReport extends ZiLinkBase {
    
    function __construct($courseid = null){
        global $CFG,$DB;
        
        $this->course = new stdClass();
        $this->course->id = $courseid;
        
        $this->data = new ZiLinkData();

        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $this->person = new ZiLinkPerson();
    }
    
    private function LoadJavaScript()
    {
        global $CFG,$PAGE,$USER;
        
        $jsdata = array($this->httpswwwroot,$this->course->id, $USER->id,sesskey());
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_homework',
                        'fullpath'  =>  '/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

        $PAGE->requires->js_init_call('M.local_zilink_homework.init', $jsdata, false, $jsmodule);
        
    }

    public function View($args)
    {
        if($args['action'] == 'list')
        {
            return $this->ViewReportList($args);
        }        
    }    

    public function ViewReportList($args)
    {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;
        
        $this->LoadJavaScript();
        require_once($CFG->libdir . '/coursecatlib.php');

       
        $reports = array();
        
        $cats = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root));
        
        $role = $DB->get_record('role',array('shortname' => 'editingteacher'));
        $mod = $DB->get_record('modules',array('name' => 'zilinkhomework'));
        
        $categories = array();
        $cohorts = array();
        $teachers = array();
        
         require_once(dirname(__FILE__) . '/pages/forms/filter_report.php');
        
        $args['homeworksetperiodstart'] = 0;
        $args['homeworksetperiodend'] = 0;
        
        $mform = new zilink_homework_report_filter_report_form();
        if($data = $mform->get_data())
        {
            if(isset($data->datefilterenabled) ){
            $args['homeworksetperiodstart'] = $data->homeworksetperiodstart;
            $args['homeworksetperiodend'] = $data->homeworksetperiodend;
            }
        }

        foreach($cats as $category)
        {
            if(($args['category'] <= 0 || $args['category'] ==  $category->id)) {
                
                $permissions = array('local/zilink:homework_report_subject_leader' => context_coursecat::instance($category->id),
                                     'local/zilink:homework_report_subject_leader' => context_system::instance(),
                                     'local/zilink:homework_report_senior_management_team' => context_coursecat::instance($category->id),
                                     'local/zilink:homework_report_senior_management_team' => context_system::instance());
               
                foreach ($permissions as $permission => $context)
                {
                    $categories[(string)$category->id] = $category->name;
                    if($this->person->Security()->IsAllowed($permission)) 
                    //if(has_capability($permission, $context, $USER,false))
                    {
                        $cat = coursecat::get($category->id);
                        $courses = $cat->get_courses(array('recursive' => 2));
                        
                        foreach($courses as $course)
                        {
                            if($course->visible == 1) 
                            {
                                
                                $count = $DB->count_records('course_modules', array('course' => $course->id, 'module' => $mod->id)); 
                                
                                $records = $DB->get_records('enrol', array('courseid' => $course->id, 'roleid' => 5, 'enrol' => 'zilink_cohort'));
                                foreach ($records as $record) {
                                    if($args['cohort'] <= 0 || $args['cohort'] ==  $record->customint1) {
                                        
                                        $cohort = $DB->get_record('cohort',array('id' => $record->customint1));
                                        
                                        if (strlen($cohort->idnumber) == 32) {
                                            
                                            $cohorts[(string)$cohort->id] = $cohort->name;
                                            
                                            $found = false;
                                            $cohort_teachers = $DB->get_records('zilink_cohort_teachers', array('cohortid' => $cohort->id));
                                            
                                            if($cohort_teachers)
                                            {                                                   
                                                foreach($cohort_teachers as $teacher)
                                                {
                                                    if($args['teacher'] <= 0 || $args['teacher'] ==  $teacher->userid) {
                                                        $found = true;
                                                        $user = $DB->get_record('user',array('id' => $teacher->userid));
                                                        $fullname = fullname($user);
                                                        $teachers[$user->id] = $fullname;
                                                        
                                                        if(!isset($reports[$category->id][$cohort->id]) || !in_array($fullname,$reports[$category->id][$cohort->id]['teachers'] )) {
                                                            $reports[$category->id][$cohort->id]['teachers'][] = $fullname;
                                                            $reports[$category->id][$cohort->id]['homework'] = $count ;
                                                        }
                                                    }
                                                }
                                                
                                                if(!$found) {
                                                    unset($reports[$category->id][$cohort->id]);
                                                }
                                            } else if ($args['teacher'] > 0) {
                                                unset($reports[$category->id][$cohort->id]);
                                            } else {
                                                $reports[$category->id][$cohort->id]['teachers'] = array('None');
                                                $reports[$category->id][$cohort->id]['homework'] = $count ;
                                            }
                                            
                                 
                                        }
                                    } 
                                }
                            }
                        }
                    }
                }
            } 
        }

        $categories = array('-1' => 'All') + $categories;
        $cohorts = array('-1' => 'All') + $cohorts;
        $teachers = array('-1' => 'All') + $teachers;
        
       
        
        $mform = new stdClass();
        $mform = new zilink_homework_report_filter_report_form('',array('cid' => $this->course->id, 
                                                                        'categories' => $categories, 
                                                                        'cohorts' => $cohorts, 
                                                                        'teachers' => $teachers, 
                                                                        'categoryid' => $args['category'],
                                                                        'cohortid' => $args['cohort'],
                                                                        'teacherid' => $args['teacher'],
                                                                        'homeworksetperiodstart' => $args['homeworksetperiodstart'],
                                                                        'homeworksetperiodend' => $args['homeworksetperiodend']
                                                                        ));
        
        $content = $mform->Display();
        
        require_once(dirname(__FILE__) . '/pages/forms/select_report.php');
        $mform2 = new stdClass();
        $mform2 = new zilink_report_writer_select_report_form('',array('cid' => $this->course->id, 'reports' => $reports, 'homeworklist' => $this->ViewHomework($args),'homeworksetperiodstart' => $args['homeworksetperiodstart'],
                                                                        'homeworksetperiodend' => $args['homeworksetperiodend']));
        
        return $content.$mform2->Display();
    }


    public function ViewHomework($args)
    {
        
        global $CFG,$DB,$PAGE;
        
        require_once($CFG->libdir . '/coursecatlib.php');
        $content = '';
        
        if($args['cohort'] < 0)
        {
            $content .= '<div class="stselect" id="homeworklist" style="margin:auto" />';
            $content .= get_string('homework_select_cohort','local_zilink');
            $content .= '</div>';
            return $content;
        }
        
        //$progressparams = array('id' => 'zilink_report_update_progress', 'class' => 'zilink_report_update_progress','src' => $PAGE->theme->pix_url('i/loading_small', 'moodle'),'alt' => get_string('timetable_loading', 'local_zilink'));
        //$content .= html_writer::empty_tag('img', $progressparams);
        //$progressparams = array('id' => 'zilink_report_update_failed', 'class' => 'zilink_report_update_progress','src' => $PAGE->theme->pix_url('i/cross_red_big', 'moodle'),'alt' => get_string('timetable_updatefailed', 'local_zilink'));
        //$content .= html_writer::empty_tag('img', $progressparams);
        //$progressparams = array('id' => 'zilink_report_update_success', 'class' => 'zilink_report_update_progress','src' => $PAGE->theme->pix_url('i/tick_green_big', 'moodle'),'alt' => get_string('timetable_updatesuccess', 'local_zilink'));
        //$content .= html_writer::empty_tag('img', $progressparams);
        
        $content .= '<div class="stselect" id="homeworklist" style="margin:auto">';
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '100%';
        $table->head        = array(get_string('cohort','cohort'),get_string('course'),get_string('homework','local_zilink'),get_string('created','local_zilink'),get_string('datedue','local_zilink'));
        $table->align       = array('left', 'left', 'center', 'center','center');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $cells = array();
        
        $cats = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root));
        $found = false;
        foreach($cats as $category)
        {
            $cat = coursecat::get($category->id);
            $courses = $cat-> get_courses(array('recursive' => 2));

            foreach($courses as $course)
            {
                if($course->visible == 1) 
                {

                    $records = $DB->get_records('enrol', array('courseid' => $course->id, 'roleid' => 5, 'enrol' => 'zilink_cohort'));
                    foreach ($records as $record) {
                            
                            $cohort = $DB->get_record('cohort',array('id' => $record->customint1));
                            
                            $mods = zilink_get_all_instances_in_course('zilinkhomework',$course, $args['uid'], true);
                            
                            if (strlen($cohort->idnumber) == 32 && $args['cohort'] == $cohort->id) 
                            {
                                
                                foreach($mods as $mod)
                                {
                                       $available = false;
                                       $due ='';
                                       if($mod->visible == 1)
                                       {
                                           $mod->created = (int)$mod->created;
                                           
                                           if($mod->completionexpected > 0) {
                                               if( $args['homeworksetperiodstart'] == 0 || $args['homeworksetperiodstart'] < $mod->created && $mod->created < $args['homeworksetperiodend']) {
                                                $available = true;
                                                $due = date('d/m/Y',$mod->completionexpected);
                                                }
                                           }
                                           else if($mod->availability == null) {
                                               
                                               if( $args['homeworksetperiodstart'] == 0 || $args['homeworksetperiodstart'] < $mod->created && $mod->created < $args['homeworksetperiodend']) {
                                                    $available = true;
                                                    $due  = 'Open Ended';
                                               } 
                                           } else {
                                                   
                                                   
                                                   $availibility = json_decode($mod->availability);
                                                   $found = false;
                                                   if(is_array($availibility->c))
                                                   {
                                                       if(empty($availibility->c)) 
                                                       {
                                                           if( $args['homeworksetperiodstart'] == 0 || $args['homeworksetperiodstart'] < $mod->created && $mod->created < $args['homeworksetperiodend']) {
                                                                $available = true;
                                                                $due  = 'Open Ended';
                                                            } 

                                                       } else {
                                                      
                                                           foreach($availibility->c as $condition)
                                                           {
                                                               if($condition->type == 'date' && $condition->d == '<')
                                                               {
                                                                   if( $args['homeworksetperiodstart'] == 0 ||  $args['homeworksetperiodstart'] < $condition->t && $condition->t < $args['homeworksetperiodend']) {
                                                                $available = true;
                                                                $due  = 'Open Ended';
                                                            } 
                                                                   $found = true;
                                                                   $cells[] = date('d/m/Y',$condition->t);
                                                               } 
                                                           }
                                                       }
                                                   } else {
                                                       if($args['homeworksetperiodstart'] == 0 ||  $args['homeworksetperiodstart'] < $mod->created && $mod->created < $args['homeworksetperiodend']) {
                                                                $available = true;
                                                                $due  = 'Open Ended';
                                                            } 
                                                   }
                                               }
                                           }

                                            if($available) {
                                                $cells[] = $cohort->name;
                                                $cells[] = $course->fullname;
                                                $cells[] = '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ';
                                                $cells[] = date('d/m/Y',$mod->created);
                                                $cells[] = $due;
                                                $found = true;
                                            }
                                       
                                }
                        } 
                     
                    } 
                }
            }
        }
        
        if(!$found)
        {
            $cells[] = 'No homework set for this period';
            $cells[] = '';
            $cells[] = '';
            $cells[] = '';
            $cells[] = '';
        }
        
        
        $table->data = array_chunk($cells, 5);
        $content .= html_writer::table($table);
        $content .= '</div>';    
        return $content;
    }
    
    public function ViewPublishedReports($args)
    {
        global $CFG,$DB;
        
        $allowedSessions = explode(',', $CFG->zilink_data_manager_sessions_allowed);
        $assessmentSessions = $this->data->GetGlobalData('assessment_sessions',true);
        $sessions = array();
        foreach($assessmentSessions->sessions->AssessmentSession as $session)
        {
            if(in_array($session->Attribute('RefId'),$allowedSessions))
            {
                foreach ( $session->SIF_ExtendedElements->SIF_ExtendedElement as $sifextendedelement ) 
                {
                
                    if ( $sifextendedelement->Attribute('Name') == 'ResultSetId' || $sifextendedelement->Attribute('Name') == 'LocalId') {
                        $rsid=$sifextendedelement;
                    }
                    if ( $sifextendedelement->Attribute('Name') == 'Name') {
                        $sessions[$session->Attribute('RefId')]=strval($sifextendedelement);
                    }
                }
            }
        }
        asort($sessions); 
        
        $category = ($args['categoryid'] <> 0) ? 'AND rwr.subjectid = '.$args['categoryid']. ' ' : '';
        
        $sql = 'SELECT rwr.id as reportid,rwr.subjectid, subject.name as subject,rwr.yearid, year.name as year, rwr.cohortid, cohort.name as cohort, rwr.assessmentsessionrefid, rwd.userid, rwr.published 
                FROM    {zilink_report_writer_data} rwd, 
                        {zilink_report_writer_reports} rwr,
                        {course_categories} subject,
                        {course_categories} year,
                        {cohort} cohort
                WHERE   rwd.reportid = rwr.id
                AND     rwd.userid = :userid
                AND     rwr.published = 1
                AND     subject.id = rwr.subjectid
                AND     year.id = rwr.yearid
                AND     rwd.status = 0
                AND     cohort.id = rwr.cohortid
                '.$category.'
                GROUP BY rwd.userid';
                    
        $reports = $DB->get_records_sql($sql,array('userid' => $args['user']->id)); 
        
        require_once(dirname(__FILE__) . '/pages/forms/view_reports.php');
        $mform = new stdClass();
        $mform = new zilink_report_writer_view_report_form('',array('cid' => $this->course->id, 'reports' => $reports, 'sessions' => $sessions, 'args' => $args ));
        
        return $mform->display();
    }

    public function ViewStudentPublishedReport($args)
    {
        global $CFG, $DB, $USER;
        
            $report = $DB->get_record('zilink_report_writer_reports', array('id' => $args['rid']));
            $components = array();
            for($i=1; $i <= 6; $i++)
            {
                $components[$i] = $this->ComponentType($report->{'component'.$i});
                if($components[$i]['type'] == 'list')
                {
                    $components[$i]['values'] = $this->ComponentGradeSet($components[$i]['gradeset']);
                }
            }
        
            $sql =  "SELECT     * 
                    FROM        {zilink_report_writer_data} 
                    WHERE       reportid=:reportid
                    AND         userid = :userid
                    AND         status = 0
                    ORDER BY    created desc";
                    
            $user = $args['user'];
            $pupilallreportdata=$DB->get_records_sql($sql,array('reportid' => $report->id, 'userid' => $user->id));
            $pupilreportdata = array();
            
            foreach ($pupilallreportdata as $pupilallreportdatum) 
            {
                $pupilreportdata[$pupilallreportdatum->setting]=$pupilallreportdatum->value;
            }
            
            $details = $this->person->GetPersonData(array('details','extended_details'), $args['user']->idnumber);
            
            require_once(dirname(__FILE__) . '/pages/forms/view_report.php');
            $mform = new stdClass();
            $mform = new zilink_report_writer_view_report_form('',array('cid' => $this->course->id, 'rid' => $args['rid'], 'uid' => $user->id, 'cohortid' => $report->cohortid, 'components' => $components, 'details' => $details, 'reportdata' => $pupilreportdata));
            
            return $mform->display();
    }
    
    private function IsCohortSignOff($type, $cohortid)
    {
        global $CFG, $DB, $USER;
        
        require_once($CFG->libdir . '/coursecatlib.php');
        
        $componentGroups = $this->data->GetGlobalData('assessment_result_component_groups',true);
            
        $allowedComponentGroups = explode(',', $CFG->zilink_data_manager_component_groups_allowed);
        $allowedSubjects = array();
        
        $components = array();
        foreach($componentGroups->componentgroups->AssessmentResultComponentGroup as $group)
        {
            if(in_array((string)$group->Attribute('RefId'),$allowedComponentGroups))
            {
                $parts = explode('::',(string)$group->Name);
                if(count($parts) > 1)
                {
                    $allowedSubjects[] = trim($parts[1]);
                }
            }
        }
        
        require_once($CFG->dirroot . "/course/lib.php");
            require_once($CFG->libdir . '/coursecatlib.php');
        
        $cat = coursecat::get($CFG->zilink_category_root);
        $categories = $cat->get_children(array('recursive' => 1));
        //$categories = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root));
        
        
        foreach($categories as $category)
        {
            
            if($type == 'sl')
            {
                $permissions = array( 'local/zilink:report_writer_subject_leader_edit' => array( context_coursecat::instance($category->id), context_system::instance()));
            } 
            else if($type = 'smt')
            {
                $permissions = array( 'local/zilink:report_writer_senior_management_team_edit' => array( context_coursecat::instance($category->id), context_system::instance()));
                //$permissions = array( context_coursecat::instance($category->id) => 'local/zilink:report_writer_senior_management_team_edit' => ,
                //                       context_system::instance() => 'local/zilink:report_writer_senior_management_team_edit' );
            }
            
            foreach ($permissions as $permission => $contexts)
            {
                foreach($contexts as $context) {
                    if(has_capability($permission, $context, $USER,false))
                    {
                        if(in_array(trim($category->name),$allowedSubjects))
                        {
                            $cat = coursecat::get($category->id);
                            $courses = $cat-> get_courses(array('recursive' => 2));
                            
                            foreach($courses as $course) {
                            
                                if(strlen($course->idnumber) == 32) {
                                
                                    if($cohorts = $DB->get_records('cohort', array('idnumber' => $course->idnumber))) {
                                    
                                        foreach($cohorts as $cohort) {
                                        
                                            if($cohort->id == $cohortid) {
                                            
                                                return true;
                                            }
                                        }
                                    }   
                                } else {
                                    $records = $DB->record_exists('enrol', array('courseid' => $course->id, 'roleid' => 5, 'enrol' => 'zilink_cohort' ,'customint1' => $cohortid));
                                    
                                    if($records) {
                                        return true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
    
    private function ComponentType($componentrefid)
    {
        global $CFG;
        
        $componentGroups = $this->data->GetGlobalData('assessment_result_component_groups',true);
        $components = $this->data->GetGlobalData('assessment_result_components',true);
            
        $allowedComponentGroups = explode(',', $CFG->zilink_data_manager_component_groups_allowed);
        $allowedComponents = explode(',', $CFG->zilink_data_manager_components_allowed);
        $allowedSubjects = array();
        
        foreach($componentGroups->componentgroups->AssessmentResultComponentGroup as $group)
        {
            if(in_array((string)$group->Attribute('RefId'),$allowedComponentGroups))
            {
                
                foreach($group->ComponentList->AssessmentResultComponentRefId as $refid)
                {
                    if(in_array((string)$refid,$allowedComponents) && (string)$refid == $componentrefid)
                    {
                        $parts = explode('::',(string)$group->Name);
                        
                        if(count($parts) > 1)
                        {
                            switch (strtolower(trim($parts[2]))) 
                            {
                                case 'attainment':
                                case 'current':
                                case 'predicted':
                                case 'target':
                                case 'targets':
                                case 'tagets':
                                case 'behaviour':
                                case 'readiness':
                                    {
                                        foreach($components->components->AssessmentResultComponent as $component)
                                        {
                                            if((string)$component->Attribute('RefId') == $componentrefid)
                                            {
                                                return array('label' => trim($parts[2]), 'type' => 'list', 'gradeset' => (string)$component->AssessmentResultGradeSetRefId);
                                            }
                                        }  
                                    }
                                case 'comment':
                                    return array('label' => trim($parts[2]), 'type' => 'editor');
                                default:
                                    return array('label' => get_string('report_writer_not_collected','local_zilink'), 'type' => 'none');
                            }
                        }
                    }
                }
            }
        }
        return array('label' => get_string('report_writer_not_collected','local_zilink'), 'type' => 'none');
    }
    
    private function ComponentGradeSet($refid)
    {
        $componentGroups = $this->data->GetGlobalData('assessment_gradesets',true);

        $options = array();
        foreach($componentGroups->gradesets->gradeset as $gradeset)
        {
            if($gradeset->Attribute('refid') == $refid)
            {
                foreach($gradeset->grade as $grade)
                {
                    $options[$grade->Attribute('title')] = $grade->Attribute('title');
                }
            }
        }
        return $options;
    }
    
    public function DefaultArguments($default_args,$args)
    {               
        foreach ($default_args as $default_arg => $value)
        {
            if(!isset($args[$default_arg]))
            {
                $args[$default_arg] = $value;
            }
        }
        
        return $args;
    }
    
}
