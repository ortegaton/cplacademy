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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/core/data.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/core/person.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/core/base.php');
 
class ZiLinkReportWriter extends ZiLinkBase {
    
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
        global $CFG,$PAGE;
        
        $jsdata = array($this->httpswwwroot,$this->course->id, sesskey());
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_timetable',
                        'fullpath'  =>  '/local/zilink/plugins/report_writer/interfaces/'.$CFG->zilink_report_writer_interface.'/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

        $PAGE->requires->js_init_call('M.local_zilink_report_writer.init', $jsdata, false, $jsmodule);
        
    }

    public function View($args)
    {
        if($args['action'] == 'list')
        {
            return $this->ViewReportList($args);
        }        
        else if($args['action'] == 'writereport')
        {
            return $this->WriteReport($args);
        }
        
    }    

    public function WriteReport($args)
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
                    
            $pupilallreportdata=$DB->get_records_sql($sql,array('reportid' => $report->id, 'userid' => $args['uid']));
            $pupilreportdata = array();
            
            foreach ($pupilallreportdata as $pupilallreportdatum) 
            {
                $pupilreportdata[$pupilallreportdatum->setting]=$pupilallreportdatum->value;
            }  
            
            $signoffs = array(  'teachersignoff' => array('permission' => 1,
                                                          'value' => (isset($pupilreportdata['teachersignoff']) ? $pupilreportdata['teachersignoff'] : 0)),
                                'subjectsignoff' => array('permission' => $this->IsCohortSignOff('sl',$report->cohortid),
                                                          'value' => (isset($pupilreportdata['subjectsignoff']) ? $pupilreportdata['subjectsignoff'] : 0)),
                                'smtsignoff' => array('permission' => $this->IsCohortSignOff('smt',$report->cohortid),
                                                          'value' => (isset($pupilreportdata['smtsignoff']) ? $pupilreportdata['smtsignoff'] : 0)));
            
            $details = $this->person->GetPersonData(array('details','extended_details'), $DB->get_record('user', array('id' => $args['uid']))->idnumber);
            
            require_once(dirname(__FILE__) . '/pages/forms/write_report.php');
            $mform = new stdClass();
            $mform = new zilink_report_writer_write_report_form('',array('cid' => $this->course->id, 'rid' => $args['rid'], 'uid' => $args['uid'], 'cohortid' => $report->cohortid, 'components' => $components, 'signoffs' => $signoffs, 'details' => $details, 'reportdata' => $pupilreportdata));
            
            if($data = $mform->get_data())
            {

                $report_entry = new Object();
                $report_entry->reportid = $args['rid'];
                $report_entry->userid = $args['uid'];
                $report_entry->enteredby = $USER->id;
                $report_entry->status = 0;
                $report_entry->created = time();
                
                for ($i=1; $i<= 6; $i++) 
                {
                    if ( isset($data->{'component'.$i})) 
                    {
                        $report_entry->setting = 'component'.$i;
                        
                        if(is_array($data->{'component'.$i})){
                            $report_entry->value = $data->{'component'.$i}['text'];
                        }
                        else {
                            $report_entry->value = $data->{'component'.$i};
                        }
                        $DB->insert_record('zilink_report_writer_data', $report_entry, true);
                    }
                }

                $signoffs = array( 'teachersignoff', 'subjectsignoff', 'smtsignoff');
                
                foreach ( $signoffs as $signoff ) 
                {
                        $report_entry->setting = $signoff;
                        $report_entry->value = $data->$signoff ;
                        $DB->insert_record('zilink_report_writer_data', $report_entry, true);
                }
                
                $where = 'reportid = :reportid AND userid = :userid AND created != :created';
                $DB->delete_records_select('zilink_report_writer_data',$where, array('reportid' => $args['rid'],
                                                                                'userid' => $args['uid'],
                                                                                'created' => $report_entry->created));
                
                $urlparams = array('sesskey' => sesskey(), 'cid' => $this->course->id, 'rid' => $args['rid'], 'cohortid' => $report->cohortid);
                redirect(new moodle_url('/local/zilink/plugins/report_writer/interfaces/default/pages/view.php', $urlparams), get_string('report_writer_report_saved', 'local_zilink'), 1);
            } 
            return $mform->display();
    }

    public function ViewReportList($args)
    {
        global $CFG, $OUTPUT, $PAGE, $USER, $COURSE, $DB;
        
        $this->LoadJavaScript();
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
        
        $reports = array();
        
        $categories = $DB->get_records('course_categories',array('parent' => $CFG->zilink_category_root));
        
        foreach($categories as $category)
        {
            $permissions = array(   'local/zilink:report_writer_subject_leader_edit' => context_coursecat::instance($category->id),
                                    'local/zilink:report_writer_subject_leader_edit' => context_system::instance(),
                                    'local/zilink:report_writer_senior_management_team_edit' => context_coursecat::instance($category->id),
                                    'local/zilink:report_writer_senior_management_team_edit' => context_system::instance());
            
            foreach ($permissions as $permission => $context)
            {
                if(has_capability($permission, $context, $USER,false))
                {
                    if(in_array(trim($category->name),$allowedSubjects))
                    {
                        $cat = coursecat::get($category->id);
                        $courses = $cat-> get_courses(array('recursive' => 2));
                        foreach($courses as $course)
                        {
                            if(strlen($course->idnumber) == 32)
                            {
                                if($cohorts = $DB->get_records('cohort', array('idnumber' => $course->idnumber)))
                                {
                                    foreach($cohorts as $cohort)
                                    {
                                        if(!isset($reports[$cohort->id]) || !in_array($course->id,$reports[$cohort->id]))
                                        {
                                            $reports[$category->id][$cohort->id] = 1;
                                        }
                                    }
                                }   
                            }
                        }
                    }
                }
            }
        }
        
        
        $courses = enrol_get_users_courses($USER->id, true);
        
        if(!empty($courses)) {
            foreach ($courses as $id=> $course) { 
            
                //context_instance_preload($course);
                if (!$context = context_course::instance($id)) 
                {
                    unset($courses[$id]);
                    continue;
                }
                if (!has_capability('local/zilink:report_writer_subject_teacher_edit', $context, $USER->id)) 
                {
                    unset($courses[$id]);
                    continue;
                }
            }
        } else {
            
            $courses = array();
            
            require_once($CFG->dirroot . "/course/lib.php");
            require_once($CFG->libdir . '/coursecatlib.php');
            
            $cat = coursecat::get($CFG->zilink_category_root);
            $categories = $cat->get_children(array('recursive' => 1));

            foreach($categories as $category)
            {
                if($this->person->Security()->IsAllowed('local/zilink:report_writer_subject_leader_edit') || $this->person->Security()->IsAllowed('local/zilink:report_writer_senior_management_team_edit'))
                {
                    $cat = coursecat::get($category->id);
                    $cs = $cat->get_courses(array('recursive' => 2));
           
                    foreach($cs as $c) {
                         $courses[$c->id] = $c;   
                    }
                }
            }
            
        }
        
        $cohorts = array();
        foreach ($courses as $id=> $course) 
        {
            if(strlen($course->idnumber) == 32)
            {
                if($cohort = $DB->get_record('cohort',array('idnumber' => $course->idnumber)))
                {
                    $cohorts[$cohort->id] = $cohort->id;
                    /*
                    $tree = explode('/', $category->path);
                    for ($i = 1; $i < count($tree); $i++) 
                    {
                        if ($tree[$i] == $CFG->zilink_category_root) 
                        {
                            $reports[$tree[$i + 1]][$cohort->id] = 1;
                            $parent_category = $DB->get_record('course_categories', array('id' => $tree[$i + 1]));
                        }
                        elseif ($CFG->zilink_category_root == 0 && $i == 1) 
                        {
                            $reports[$tree[$i]][$cohort->id] = 1;
                        }
                    }
                     * */
                     
                }
            } else {
                $records = $DB->get_records('enrol', array('courseid' => $course->id, 'roleid' => 5, 'enrol' => 'zilink_cohort'));
                                
                foreach ($records as $record) {
                    
                    $cohort = $DB->get_record('cohort',array('id' => $record->customint1));
                    
                    if (strlen($cohort->idnumber) == 32) {
                        
                        $cohorts[$cohort->id] = $cohort->id;
                     /*
                        $tree = explode('/', $category->path);
                        for ($i = 1; $i < count($tree); $i++) 
                        {
                            if ($tree[$i] == $CFG->zilink_category_root) 
                            {
                                $reports[$tree[$i + 1]][$cohort->id] = 1;
                                $parent_category = $DB->get_record('course_categories', array('id' => $tree[$i + 1]));
                            }
                            elseif ($CFG->zilink_category_root == 0 && $i == 1) 
                            {
                                $reports[$tree[$i]][$cohort->id] = 1;
                            }
                        }
                      * 
                      */
                    }
                }
            }
        }
        
        $cohorts = array_merge($cohorts,$DB->get_records_menu('zilink_cohort_teachers', array('userid' => $USER->id), null, 'id,cohortid'));
        if($cohorts)
        {
                  
            $templates = $DB->get_records_sql('SELECT * FROM 
                                               {zilink_report_writer_reports}
                                               WHERE cohortid IN ('. implode(',',$cohorts).')');
                                                                                             
            foreach($templates as $template)
            {
                 $reports[$template->subjectid][$template->cohortid] = 1;
            }
        }
        
        $catgeories = array();
        $cohorts = array();
        
        foreach($reports as $catgeory => $courses)
        {
            $catgeories[] = $catgeory;
            $cohorts = array_merge($cohorts,array_keys($courses));
        }
        
        $reports = $DB->get_records_sql('SELECT rwr.id as reportid, subject.name as subject, subject.id as subjectid, year.name as year, year.id as yearid, c.name as cohort, c.id as cohortid FROM 
                                           {zilink_report_writer_reports} rwr, {course_categories} subject,  {course_categories} year, {cohort} c
                                           WHERE rwr.cohortid IN ('. implode(',',$cohorts).') 
                                           AND rwr.subjectid = subject.id
                                           AND rwr.yearid = year.id
                                           AND rwr.cohortid = c.id
                                           AND rwr.open = 1');
        
        
        require_once(dirname(__FILE__) . '/pages/forms/select_report.php');
        $mform = new stdClass();
        $mform = new zilink_report_writer_select_report_form('',array('cid' => $this->course->id, 'reports' => $reports, 'pupillist' => $this->ViewPupils($args)));
        
        return $mform->Display();
    }


    public function ViewPupils($args)
    {
        global $CFG,$DB,$PAGE;
        $content = '';
        
        if($args['cohortid'] == 0)
        {
            $content .= '<div class="stselect" id="pupillist" style="margin:auto" />';
            $content .= get_string('report_writer_select_cohort','local_zilink');
            $content .= '</div>';
            return $content;
        }
        
        $progressparams = array('id' => 'zilink_report_update_progress', 'class' => 'zilink_report_update_progress','src' => $PAGE->theme->pix_url('i/loading_small', 'moodle'),'alt' => get_string('timetable_loading', 'local_zilink'));
        $content .= html_writer::empty_tag('img', $progressparams);
        $progressparams = array('id' => 'zilink_report_update_failed', 'class' => 'zilink_report_update_progress','src' => $PAGE->theme->pix_url('i/cross_red_big', 'moodle'),'alt' => get_string('timetable_updatefailed', 'local_zilink'));
        $content .= html_writer::empty_tag('img', $progressparams);
        $progressparams = array('id' => 'zilink_report_update_success', 'class' => 'zilink_report_update_progress','src' => $PAGE->theme->pix_url('i/tick_green_big', 'moodle'),'alt' => get_string('timetable_updatesuccess', 'local_zilink'));
        $content .= html_writer::empty_tag('img', $progressparams);
        
        $content .= '<div class="stselect" id="pupillist" style="margin:auto">';
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '80%';
        $table->head        = array(get_string('report_writer_student_name','local_zilink'),get_string('report_writer_teacher','local_zilink'),get_string('report_writer_subject_learder','local_zilink'),get_string('report_writer_smt','local_zilink'));
        $table->align       = array('left', 'center', 'center', 'center');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $cells = array();
        
        $sql = "SELECT  c.id as cid,
                        co.id as coid,
                        c1.id as c1id,
                        c.cohortid,
                        c.userid,
                        co.name,
                        r.id as rid 
                FROM    {cohort_members} c, 
                        {zilink_report_writer_reports} r, 
                        {cohort_members} c1, 
                        {cohort} co 
                WHERE   r.open=1
                AND     r.cohortid=c1.cohortid
                AND     c.userid=c1.userid
                AND     c.cohortid=co.id 
                AND     co.id = :cohortid
                AND     r.id = :reportid
                ORDER BY    c.cohortid,c.userid"; 
                                   
        $reportpupils = $DB->get_records_sql($sql,array('cohortid' => $args['cohortid'],'reportid' => $args['rid']));
        
        foreach($reportpupils as $reportpupil)
        {
            $urlparams = array('action' => 'writereport','sesskey' => sesskey(), 'uid' => $reportpupil->userid, 'cid' => $this->course->id, 'rid' => $reportpupil->rid);
            $url = new moodle_url('/local/zilink/plugins/report_writer/interfaces/default/pages/view.php', $urlparams);
                
            $sql =  "SELECT     * 
                    FROM        {zilink_report_writer_data} 
                    WHERE       reportid=:reportid
                    AND         userid = :userid
                    AND         status = 0
                    ORDER BY    created desc";
                    
            $pupilallreportdata=$DB->get_records_sql($sql,array('reportid' => $args['rid'], 'userid' => $reportpupil->userid));
            $pupilreportdata = array();
            foreach ($pupilallreportdata as $pupilallreportdatum) 
            {
                if ( !isset($pupilreportdata[$pupilallreportdatum->setting])) {
                    $pupilreportdata[$pupilallreportdatum->setting]=$pupilallreportdatum->value;
                }
            }
            
            
            $cell = new html_table_cell();
            
            if($this->IsCohortSignOff('smt',$args['cohortid']))
            {
                $cell->text = '<b>' . html_writer::link($url,fullname($DB->get_record('user',array('id' => $reportpupil->userid)))). '</b>';
            }
            else
            {
                if($this->IsCohortSignOff('sl',$args['cohortid']))
                {
                    if ( isset($pupilreportdata['smtsignoff'] )) 
                    {
                        if ( $pupilreportdata['smtsignoff'] == 1 ) 
                        {
                            $cell->text = '<b>' . fullname($DB->get_record('user',array('id' => $reportpupil->userid))). '</b>';
                        } 
                        else 
                        {
                            $cell->text = '<b>' .  html_writer::link($url,fullname($DB->get_record('user',array('id' => $reportpupil->userid)))). '</b>';
                        }
                    }
                    else 
                    {
                        $cell->text = '<b>' .  html_writer::link($url,fullname($DB->get_record('user',array('id' => $reportpupil->userid)))). '</b>';
                    }
                }
                else
                {
                    if ( isset($pupilreportdata['subjectsignoff'] )) 
                    {
                        if ( $pupilreportdata['subjectsignoff'] == 1 ) 
                        {
                            $cell->text = '<b>' . fullname($DB->get_record('user',array('id' => $reportpupil->userid))). '</b>';
                        } 
                        else 
                        {
                            $cell->text = '<b>' .  html_writer::link($url,fullname($DB->get_record('user',array('id' => $reportpupil->userid)))). '</b>';
                        }
                    }
                    else 
                    {
                        $cell->text = '<b>' .  html_writer::link($url,fullname($DB->get_record('user',array('id' => $reportpupil->userid)))). '</b>';
                    }
                }
            }
            
            //$cell->text = '<b>' . fullname($DB->get_record('user',array('id' => $reportpupil->userid))). '</b>';
            $cell->attributes = array('class' =>'pupil-name');
            
            $cells[] = $cell;

            $cell = new html_table_cell();
            
            if ( isset($pupilreportdata['teachersignoff'] )) 
            {
                if ( $pupilreportdata['teachersignoff'] == 1 ) 
                {
                    $cell->text = '<input type="checkbox" disabled checked>';
                } else {
                    $cell->text = '<input type="checkbox" disabled>';
                }
            }
            else
            {
                $cell->text = '<input type="checkbox" disabled>';
            }
            $cell->attributes = array('class' =>'pupil-signoff');
            
            $cells[] = $cell;
            
            $cell = new html_table_cell();
            if ( isset($pupilreportdata['subjectsignoff'] )) 
            {
                if ( $pupilreportdata['subjectsignoff'] == 1 ) 
                {
                    $cell->text = '<input type="checkbox" disabled checked>';
                } else {
                    $cell->text = '<input type="checkbox" disabled>';
                }
            }
            else
            {
                $cell->text = '<input type="checkbox" disabled>';
            }
            $cell->attributes = array('class' =>'pupil-signoff');
            
            $cells[] = $cell;
            
            $cell = new html_table_cell();
            if ( isset($pupilreportdata['smtsignoff'] )) {
                if ( $pupilreportdata['smtsignoff'] == 1 ) {
                    $cell->text = '<input type="checkbox" disabled checked>';
                } else {
                    $cell->text = '<input type="checkbox" disabled>';
                }
            }
            else
            {
                $cell->text = '<input type="checkbox" disabled>';
            }
            $cell->attributes = array('class' =>'pupil-signoff');
            
            $cells[] = $cell;
        }

        $table->data = array_chunk($cells, 4);
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