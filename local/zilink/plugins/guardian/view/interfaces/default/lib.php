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

define('ZILINK_PULIL_DETAILS',-1);
define('ZILINK_SUBJECTS',-2);
define('ZILINK_OVERVIEW',-3);
define('ZILINK_TIMETABLE',-4);
define('ZILINK_INFORMATION',-5);
define('ZILINK_REPORTS',-6);
define('ZILINK_HOMEWORK',-7);
define('ZILINK_ATTENDANCE',-8);


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/data.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/person.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/base.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/panel.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/timetable/lib.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/report_writer/interfaces/'.$CFG->zilink_report_writer_interface.'/lib.php');
require_once($CFG->dirroot.'/local/zilink/lib.php');
 
class ZiLinkGuardianView extends ZiLinkBase {
    
    function __construct($offset = 0){
        global $CFG,$DB;
        
        $this->data = new ZiLinkData();
        $this->person = new ZiLinkPerson();
        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $this->offset = $offset;
        $this->people = $this->person->GetLinkedPeopleData('children',array('extended_details','details','attendance','assessment','picture','behaviour'));
        
        $count = 0;

        if(isset($this->people['children']))
        {
            foreach($this->people['children'] as $child)
            {
                if($count == $this->offset)
                {
                    $this->child = $child;
                }
                $count++;
            }
        }
        else {
            $this->child = null;
            throw new exception("No students linked to current user");
        } 
        
        parent::__construct(1);
        include(dirname(dirname(dirname(dirname(__FILE__)))).'/admin/defaults.php');
    }
    
    function GetPublishedSubjects()
    {
        global $CFG,$DB;
        
        $cats = array();
        $courses = array();
        
        $count = 0;
        
        $courses = enrol_get_users_courses($this->child->user->id);
        
        
        $count=1;
        foreach($courses as $course)
        {
            $category = $DB->get_record('course_categories',array('id' => $course->category));
            
            if(isset($category->ctxpath)) {
                $path = explode('/',$category->ctxpath);
            } else {
                $path = explode('/',$category->path);
            }

            
            if(($CFG->zilink_category_root == 0) || in_array($CFG->zilink_category_root,$path))
            {
                if(!empty($path))
                {
                    if($CFG->zilink_category_root == 0)
                    {
                        if(!in_array($path[1],$cats))// && $category->visible == 1)
                        {
                            $cats[] = $path[1];
                        }
                    }
                    else
                    {
                        $flag = false;
                        foreach( $path as $step)
                        {
                            if($flag)
                            {
                                if(!in_array($step,$cats))// && $category->visible == 1)
                                {
                                    $cats[] = $step;
                                }
                            }   
                            if($step == $CFG->zilink_category_root)
                                $flag = true;
                            else
                                $flag = false;
                                
                        }
                    }
                }
            }
        }
        $subjects = array();
        if(!empty($cats))
        {
            foreach($cats as $cat)
            {
                $subs = zilinkdeserialise($CFG->zilink_guardian_view_default_subjects_allowed);
                
                if(isset($subs[$cat]) && $subs[$cat] == 1) {
                      
                  $category = $DB->get_record('course_categories',array('id' => $cat ));
                    
                  $subjects[$category->id] = $category->name;
                }        
            }
        }
        
        return $subjects;
    }

    function GetTopLevelTabs()
    {
        global $CFG,$DB,$USER;
        
        $urlparams = array('sesskey' => sesskey(),'offset' => $this->offset);
        
        $browserow = array();
        
        $browserow[ZILINK_PULIL_DETAILS] = new tabobject(ZILINK_PULIL_DETAILS,new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/index.php', $urlparams),get_string('guardian_view_default_student_details','local_zilink'));
        $inactive = array(ZILINK_PULIL_DETAILS);
        
                           
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_overview'))
        {
            $browserow[ZILINK_OVERVIEW] = new tabobject(ZILINK_OVERVIEW, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/overview.php', $urlparams),get_string('guardian_view_default_overview', 'local_zilink'));
        }     
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_attendance_recent') || $this->person->Security()->IsAllowed('local/zilink:guardian_view_attendance_overview'))
        {
            $browserow[ZILINK_ATTENDANCE] = new tabobject(ZILINK_ATTENDANCE, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/attendance.php', $urlparams),get_string('guardian_view_default_attendance', 'local_zilink'));
        } 
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects'))
        {
            $subjects = $this->GetPublishedSubjects();
            if(!empty($subjects))
            {
                $browserow[ZILINK_SUBJECTS] = new tabobject(ZILINK_SUBJECTS, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/subjects.php', $urlparams),get_string('subjects', 'local_zilink'));
            }
        }
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_homework'))
        {
            $browserow[ZILINK_HOMEWORK] = new tabobject(ZILINK_HOMEWORK, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/homework.php', $urlparams), get_string('homework', 'local_zilink'));
        }
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_reports'))
        {
            $browserow[ZILINK_REPORTS] = new tabobject(ZILINK_REPORTS, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/reports.php', $urlparams), get_string('reports', 'local_zilink'));
        }
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_timetable'))
        {                              
            $browserow[ZILINK_TIMETABLE] = new tabobject(ZILINK_TIMETABLE, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/timetable.php', $urlparams), get_string('timetable', 'local_zilink'));
        }
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_information'))
        {
            $browserow[ZILINK_INFORMATION] = new tabobject(ZILINK_INFORMATION, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/information.php', $urlparams), get_string('information', 'local_zilink'));
        }
        
        
        
        return $browserow;
    }
    
    function PupilDetails()
    {
        $content = '';
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_PULIL_DETAILS,array(ZILINK_PULIL_DETAILS),array(ZILINK_PULIL_DETAILS),true);

        $content .= $this->GetNotificationMessage(); 
        
        $content .= '<table class="generaltable boxaligncenter zilink_table_width">
                        <tbody><tr class="r0 lastrow">
                         <td class="cell c0 lastcol" style="text-align:center;">';
        
        $content .= $this->GetStudentPhotoPanels();          
        $content .= $this->GetStudentDetailPanels();
        $content .= $this->GetTodaysTimetable();
        
        $content .= '</td></tbody></table>'; 
        $content .= '<div class="clearer"></div>';
        
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_student_details_attendance'))
        {
            $content .= '<div class="clearer"></div>';
            $content .= $this->GetStudentAttendanceSummary();
        }
        
        if(!$this->person->Security()->IsAllowed('local/zilink:guardian_view_student_details_behaviour'))
        {
            $content .= '<div class="clearer"></div>';
            $content .= $this->GetBehaviour();
        }
        
        if(!$this->person->Security()->IsAllowed('local/zilink:guardian_view_student_details_achievement'))
        {
            $content .= '<div class="clearer"></div>';
            $content .= $this->GetAchievement();
        }
        

        $content .= '<div class="clearer"></div>';
        
        return $content;
    }

    function Attendance()
    {
        $content = '';
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_ATTENDANCE,array(ZILINK_ATTENDANCE),array(ZILINK_ATTENDANCE),true);
        
        $content .= $this->GetNotificationMessage();  
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_attendance_recent'))
        {
            $content .= '<div class="clearer"></div>';
            $content .= $this->GetStudentAttendanceSummary();
        }
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_attendance_overview'))
        {
           $content .= '<div class="clearer"></div>';
           $content .= $this->GetAttendanceOverviewGraph();
        }
        
        return $content;
    }
      

    function Overview()
    {
        
        $content = '';
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_OVERVIEW,array(ZILINK_OVERVIEW),array(ZILINK_OVERVIEW),true);
        
        $content .= $this->GetNotificationMessage();  
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_overview_assessment'))
        {
            $content .= $this->GetAssessmentOverviewGraph();
        }
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_overview_attendance'))
        {
            $content .= $this->GetAttendanceOverviewGraph();
        }
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_overview_home_learning'))
        {
            $content .= $this->GetCurrentWorkOverview();
        }
        
        return $content;
    }
    
    function Subjects($tab)
    {
        global $CFG,$PAGE,$OUTPUT;
        
        $row = array();
        $content = '';
        
        $urlparams = array('sesskey' => sesskey(),'offset' => $this->offset,'id' => '0');
        $row[] = new tabobject(0, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/subjects.php', $urlparams),get_string('all'));
        
        $subjects = $this->GetPublishedSubjects();

        foreach($subjects as $id => $name)
        {
            $icon = '';
            
            if ($this->person->Security()->IsAllowed('local/zilink:guardian_view_icons') && isset($CFG->{'zilink_icon_navigation_category_icon_'.$id})) {
                $subject = $CFG->{'zilink_icon_navigation_category_icon_'.$id};
                
                if($subject <> '0') {
                    $file = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.
                            $CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject.'.*';
    
                    $pix = 'icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject;
                    
                    $icon = $OUTPUT->pix_icon($pix,
                                                                        '', 'block_zilink',
                                                                        array('height' => '24px',
                                                                             'width' => '24px',
                                                                             'style' => 'float: left; margin-right: 5px;',
                                                                             'title' => $name, 'class' => 'none'));
                }
                                                                         
            }
            
            $urlparams = array('sesskey' => sesskey(),'offset' => $this->offset,'id' => $id);
            $row[] = new tabobject($id, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/subjects.php', $urlparams),$icon.$name);
        }

        $content .= print_tabs(array($this->GetTopLevelTabs(),$row),ZILINK_SUBJECTS,array($tab),array($tab),true);
          
        $content .= $this->GetNotificationMessage();  
        
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_teacher_details'))
        {
                $content .= $this->GetSubjectTeachers($tab);
        }
        
        if ($tab == 0) {
            if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_overview_assessment'))
            {
                $content .= $this->GetAssessmentOverviewGraph();
            }
        } else {
        
            if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_assessment'))
            {
                $content .= $this->GetAssessmentSubjectGraph($tab);
            }
            if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_homework'))
            {
                $panel = new ZiLinkPanel();
                $panel->SetTitle(get_string('guardian_view_default_homework','local_zilink'));
            
                $cells = array();
                
                $cells[] = array($this->GetHomework($tab));
               
                $panel->SetWidth('100%');
                $panel->SetCSS('generaltable boxaligncenter');
                $panel->SetContent($cells);
                
                $content .= $panel->Display();
            }
            if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_submitted_work'))
            {
                $panel = new ZiLinkPanel();
                $panel->SetTitle(get_string('guardian_view_default_submitted_work','local_zilink'));
            
                $cells = array();
                
                $cells[] = array($this->GetSubjectSubmittedWork($tab));
               
                $panel->SetWidth('100%');
                $panel->SetCSS('generaltable boxaligncenter');
                $panel->SetContent($cells);
                
                $content .= $panel->Display();
    
            }
            if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_reports'))
            {
                try {
                    $jsmodule = array(
                                'name'  =>  'local_zilink_guardian_view_reports',
                                'fullpath'  =>  '/local/zilink/plugins/guardian/view/interfaces/default/module.js',
                                'requires'  =>  array('base', 'node', 'io','charts','json'),
                                'strings' => array ());
                                
                    $jsdata = array($this->httpswwwroot,$this->course->id,$this->offset,sesskey());
                    //$PAGE->requires->js_module($jsmodule);
                    $PAGE->requires->js_init_call('M.local_zilink_guardian_view_reports.init', $jsdata, false, $jsmodule);
                        
                    
                    $report_writer = new ZiLinkReportWriter($this->course->id);
                    $panel = new ZiLinkPanel();
                    $panel->SetTitle(get_string('guardian_view_default_subject_reports','local_zilink'));
            
                    $cells = array();
                    
                    $cells[] = array($report_writer->ViewPublishedReports(array('user' => $this->child->user,'categoryid' => $tab,'mode' => 'full')));
                   
                    $panel->SetWidth('68%');
                    $panel->SetCSS('generaltable boxaligncenter');
                    $panel->SetContent($cells);
                    
                    $content .= $panel->Display();
                } catch (Exception $e){}
               
            }
        }
        
         
         return $content;
    }

    function Timetable()
    {
        $content = '';
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_TIMETABLE,array(ZILINK_TIMETABLE),array(ZILINK_TIMETABLE),true);

        $content .= $this->GetNotificationMessage(); 
        
        $timetable = new ZiLinkTimetable(1);
        $content .= $timetable->GetTimetable(array('requested_by' => 'guardian_view','user_idnumber' => $this->child->user->idnumber));

        $content .= '<div class="clearer"></div>';
        
        return $content;
    }

    function Information()
    {
        global $DB,$USER;
        
        $content = '';
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_INFORMATION,array(ZILINK_INFORMATION),array(ZILINK_INFORMATION),true);
                        
        $content .= $this->GetNotificationMessage();  
        
        $guardian = $this->person->GetPersonalData('extended_details',true); 

        $guardiandetails ='';
        if(isset($guardian->extended_details->PersonalInformation->Name)) {
        $guardiandetails =  '<div style="width:20%; float: left; margin-bottom: 15px;">Name:</div><div style="width:80%; float: left; margin-bottom: 15px;">'.utf8_decode($guardian->extended_details->PersonalInformation->Name->FullName).'</div>';
        }
        if(isset($guardian->extended_details->PersonalInformation->Address)) {
            if(isset($guardian->extended_details->PersonalInformation->Address->PAON)) {
                $guardiandetails .=  '<div style="width:20%; float: left; margin-bottom: 15px;">Address:</div><div style="width:80%; float: left; margin-bottom: 15px;">'.$guardian->extended_details->PersonalInformation->Address->PAON->StartNumber.' '.$guardian->extended_details->PersonalInformation->Address->Street.'<br>';    
            }
            else {
                $guardiandetails .=  '<div style="width:20%; float: left; margin-bottom: 15px;">Address:</div><div style="width:80%; float: left; margin-bottom: 15px;">'.$guardian->extended_details->PersonalInformation->Address->Street.'<br>';
            }
            $guardiandetails .= (isset($guardian->extended_details->PersonalInformation->Address->Locality)) ? $guardian->extended_details->PersonalInformation->Address->Locality.'<br>' : ' ';
            $guardiandetails .= (isset($guardian->extended_details->PersonalInformation->Address->Town)) ? $guardian->extended_details->PersonalInformation->Address->Town.'<br>' : ' ';
            $guardiandetails .= (isset($guardian->extended_details->PersonalInformation->Address->PostCode)) ? $guardian->extended_details->PersonalInformation->Address->PostCode.'<br>' : ' ';
        }
        if(isset($guardian->extended_details->PersonalInformation->PhoneNumber))
        {
            $guardiandetails .=  '</div><br><div style="width:20%; float: left;">Phone:</div><div style="width:80%; float: left;">';
            $phones = array('H' => 'Home', 'T' => 'Home', 'M' => 'Mobile', 'W' => 'Work');
            
            foreach($guardian->extended_details->PersonalInformation->PhoneNumber as $number)
            {
                if($number->Attribute('Type') == 'H' || $number->Attribute('Type') == 'T'  || $number->Attribute('Type') == 'W' || $number->Attribute('Type') == 'M')
                {
                    $guardiandetails .= $phones[$number->Attribute('Type')].': '.$number->Number.'<br>';
                } else {
                    $guardiandetails .= $number->Number.'<br>';
                }
            }       
        }
        
        if(isset($guardian->extended_details->PersonalInformation->OtherPhoneNumberList->PhoneNumber))
        {
            $guardiandetails .=  '</div><br><div style="width:20%; float: left;">Phone:</div><div style="width:80%; float: left;">';
            $phones = array('H' => 'Home', 'M' => 'Mobile', 'W' => 'Work');
            
            foreach($guardian->extended_details->PersonalInformation->OtherPhoneNumberList->PhoneNumber as $number)
            {
                if($number->Attribute('Type') == 'H' || $number->Attribute('Type') == 'W' || $number->Attribute('Type') == 'M')
                    $guardiandetails .= $phones[$number->Attribute('Type')].': '.$number->Number.'<br>';
            }       
        }
        $guardiandetails .=  '</div></p>';  

        $studentdetails =  '<div style="width:20%; float: left; margin-bottom: 15px;">Name:</div><div style="width:80%; float: left; margin-bottom: 15px;">';
        $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Name->FullName)) ? utf8_decode($this->child->extended_details->PersonalInformation->Name->FullName).'</div>' : '</div>';
        
        if ($this->person->Security()->IsAllowed('local/zilink:guardian_view_information_student_address')) {
            $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Address->PAON->StartNumber))? '<div style="width:20%; float: left; ">Address:</div><div style="width:80%; float: left;">' : '';
            $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Address->PAON->StartNumber))? $this->child->extended_details->PersonalInformation->Address->PAON->StartNumber .' ' : '';
            $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Address->Street))? $this->child->extended_details->PersonalInformation->Address->Street.'<br>':'';
            $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Address->Locality)) ? $this->child->extended_details->PersonalInformation->Address->Locality.'<br>' : '';
            $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Address->Town)) ? $this->child->extended_details->PersonalInformation->Address->Town.'<br>' : ' ';
            $studentdetails .= (isset($this->child->extended_details->PersonalInformation->Address->PostCode)) ? $this->child->extended_details->PersonalInformation->Address->PostCode.'<br>' : '';
        }
        
        $cells = array_merge(array($guardiandetails),array($studentdetails));

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array('Guardian Details','Student Details');
        $table->align       = array('left', 'left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->attributes['class'] = 'generaltable boxaligncenter zilink_table_width';
        
        $table->data = array_chunk($cells, 2);
        $content .= html_writer::table($table);
        return $content;
    }

    function Reports($args)
    {
        global $DB,$USER,$CFG,$PAGE;
        
        $content = '';
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_REPORTS,array(ZILINK_REPORTS),array(ZILINK_REPORTS),true);
        
        $content .= $this->GetNotificationMessage();  
        
        try{
            
       
            $reports = new ZiLinkReportWriter($this->course->id);
            
            if($args['action'] == 'list')
            {
                $jsmodule = array(
                            'name'  =>  'local_zilink_guardian_view_reports',
                            'fullpath'  =>  '/local/zilink/plugins/guardian/view/interfaces/default/module.js',
                            'requires'  =>  array('base', 'node', 'io','charts','json'),
                            'strings' => array ());
                            
                $jsdata = array($this->httpswwwroot,$this->course->id,$this->offset,sesskey());
                //$PAGE->requires->js_module($jsmodule);
                $PAGE->requires->js_init_call('M.local_zilink_guardian_view_reports.init', $jsdata, false, $jsmodule);
            
                //$content .= print_tabs(array($this->GetTopLevelTabs(),array(new tabobject('','',''))),ZILINK_REPORTS,array(ZILINK_REPORTS),array(ZILINK_REPORTS),true);
                $content .= $this->GetNotificationMessage();
                $content .= $reports->ViewPublishedReports(array('user' => $this->child->user, 'categoryid' => 0, 'mode' => 'normal', 'requested_by' => 'guardian_view'));
            }
            else if($args['action'] == 'view')
            {
            
                $content .= $reports->ViewStudentPublishedReport(array('user' => $this->child->user, 'requested_by' => 'guardian_view','rid' => $args['rid']));
            }
        } catch (Exception $e)
        {
            
            $table              = new html_table();
            $table->cellpadding = '10px';    
            $table->width       = '68%';
            $table->head        = array('Reports');
            $table->align       = array('left');
            $table->border      = '2px'; 
            $table->tablealign  = 'center';
            
            $cells[] = get_string('guardian_view_default_no_reports_published','local_zilink');
            
            $table->data = array_chunk($cells, 1);
            $content .=  html_writer::table($table);  
        }
        
        return $content;
    }

    public function Homework($tab)
    {
        global $DB,$USER,$CFG,$PAGE,$OUTPUT;
        
        $content = '';
        
        $row = array();
        
        $urlparams = array('sesskey' => sesskey(),'offset' => $this->offset,'id' => '0');
        $row[] = new tabobject(0, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/homework.php', $urlparams),get_string('all'));
        
        $subjects = $this->GetPublishedSubjects();

        foreach($subjects as $id => $name)
        {
            if ($this->person->Security()->IsAllowed('local/zilink:guardian_view_icons') && isset($CFG->{'zilink_icon_navigation_category_icon_'.$id})) {
                $subject = $CFG->{'zilink_icon_navigation_category_icon_'.$id};
                $icon = '';
                if($subject <> '0') 
                {
                    $file = $CFG->dirroot.'/blocks/zilink/pix/icon_navigation/'.
                            $CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject.'.*';
    
                    $pix = 'icon_navigation/'.$CFG->zilink_icon_navigation_iconset.'/subjects/'.$subject;
                    
                    $icon = $OUTPUT->pix_icon($pix,
                                                                        '', 'block_zilink',
                                                                        array('height' => '24px',
                                                                             'width' => '24px',
                                                                             'style' => 'float: left; margin-right: 5px;',
                                                                             'title' => $name, 'class' => 'none'));
                }                                    
            }

            $urlparams = array('sesskey' => sesskey(),'offset' => $this->offset,'id' => $id);
            $row[] = new tabobject($id, new moodle_url($this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/homework.php', $urlparams),$name);
        }
        
        $content .= print_tabs(array($this->GetTopLevelTabs(),$row),ZILINK_HOMEWORK,array($tab),array($tab),true);
        $content .= $this->GetNotificationMessage();  
        
        $content .= $this->GetHomework($tab);
        
        return $content;
    }
    
    function GetBehaviour()
    {
        $content ='';

        if(isset($this->child->behaviour))
        {
            $panel = new ZiLinkPanel();
            $panel->SetTitle(get_string('guardian_view_default_recent_behaviour','local_zilink'));
            $panel->SetWidth('100%');
            $panel->SetCSS('generaltable boxaligncenter zilink_table_width');
            
            $table                  = new html_table();  
            $table->width           = '68%';
            $table->align           = array('left','left','left','left');
            $table->headspan        = array(2);
            $table->border          = '2px'; 
            $table->tablealign      = 'center';
            
            $rows = array();
            $row = array();
            $count = 0;
            
            foreach($this->child->behaviour->LearnerBehaviourIncident as $incident)
            {
                if((string)$incident->SIF_ExtendedElements->SIF_ExtendedElement == 'Behaviour') 
                {
                    if($count == 0)
                    {
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Date';
                        $row[] = $cell;
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Incident';
                        $row[] = $cell;
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Location';
                        $row[] = $cell;
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Action';
                        $row[] = $cell;
                    }
                    
                    $row[] = date('d/m/Y',strtotime($incident->Incident->Date)); 
                    $row[] = (string)$incident->BehaviourType->SubClassification;
                    $row[] = (string)$incident->Incident->Location;
                    $row[] = (string)$incident->Participants->Learners[0]->Learner->Actions->Action->SubClassification;
                }   
                $count++;
            }
            
            $table->data = array_chunk($row, 4);
            $row = array();
            $row[] = html_writer::table($table);
            
            $panel->SetContent(array_chunk($row, 1));
            $content = $panel->Display();
        }

        return $content;
    }
    
    function GetAchievement()
    {
        $content ='';

        if(isset($this->child->behaviour))
        {
            
            $panel = new ZiLinkPanel();
            $panel->SetTitle(get_string('guardian_view_default_recent_achievement','local_zilink'));
            $panel->SetWidth('100%');
            $panel->SetCSS('generaltable boxaligncenter zilink_table_width');
            
            $table                  = new html_table();  
            $table->width           = '68%';
            //$table->head            = array(get_string('guardian_view_default_recent_achievement','local_zilink'));
            $table->align           = array('left','left','left','center');
            $table->headspan        = array(2);
            $table->border          = '2px'; 
            $table->tablealign      = 'center';
            
            $rows = array();
            $row = array();
            $count = 0;
            
            foreach($this->child->behaviour->LearnerBehaviourIncident as $incident)
            {
                if((string)$incident->SIF_ExtendedElements->SIF_ExtendedElement == 'Achievement') 
                {
                    if($count == 0)
                    {
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Date';
                        $row[] = $cell;
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Type';
                        $row[] = $cell;
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Location';
                        $row[] = $cell;
                        $cell = new html_table_cell();
                        $cell->header = true;
                        $cell->text = 'Points';
                        $row[] = $cell;
                    }
                    
                    $row[] = date('d/m/Y',strtotime($incident->Incident->Date)); 
                    $row[] = (string)$incident->BehaviourType->SubClassification;
                    $row[] = (string)$incident->Incident->Location;
                    $row[] = (string)$incident->BehaviourType->Weighting;
                 
                    $count++;
                }
            }
            
            $table->data = array_chunk($row, 4);
            $row = array();
            $row[] = html_writer::table($table);
            
            $panel->SetContent(array_chunk($row, 1));
            $content = $panel->Display();
        }
        
        return $content;
    }
    
    private function GetNotificationMessage()
    {
        global $CFG;
        
        $cells = array();

        $pages = zilinkdeserialise($CFG->zilink_guardian_view_default_display_notification);
        
        if(empty($CFG->zilink_guardian_view_default_notification) || !isset($pages[str_replace(".php", "", basename($_SERVER['PHP_SELF']))]) || $pages[str_replace(".php", "", basename($_SERVER['PHP_SELF']))] == 0)
            return '';
        
        $cells[] = $CFG->zilink_guardian_view_default_notification;
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array('Important Notification');
        $table->align       = array('left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->data = array_chunk($cells, 1);
        return html_writer::table($table);  
    }

    private function GetAssessmentSubjectGraph($id)
    {
        global $CFG,$DB,$PAGE;
        
        $data = array();

        $gradesets = $this->data->GetGlobalData('assessment_gradesets');
         
        $count = 1;

        if(!isset($this->child->assessment->assessmenthistory->assessments->assessment))
            return '';
            
        if(!isset($gradesets->gradesets))
            return '';
        
        $category = $DB->get_record('course_categories', array('id' => $id));  
        
        $max = 0;
        $min = 0;
        
        $gradelables = array();
        $gradetypestrings = array();
        $links = array();
        $attainment = array();
        $targets = array();
        
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
        
        $sessionOrder = explode(',', $CFG->zilink_data_manager_sessions_order);
        
        foreach($this->child->assessment->assessmenthistory->assessments->assessment as $assessment)
        {
            if($assessment->Attribute('subject') == $category->name)
            {
                if($assessment->Attribute('type') == 'Grade' && array_key_exists($assessment->Attribute('session'),$sessions))
                {
                
                    $result = $assessment->Attribute('result');
                    $refid = $assessment->Attribute('gradeset');
                    
                    $grade = new stdClass();
                    $gradesets = @simplexml_load_string($gradesets->asXML(),'simple_xml_extended');
                    $grade = $gradesets->xpath("//gradeset[@refid='".$assessment->Attribute('gradeset')."']/grade[@title='".$result."']");
                    $grade = $grade[0];
                    
                    if(!is_object($grade))
                    {
                        return '';
                    }
                    
                    $resulttype = '';
                    switch(strtolower(str_replace(' ','',$assessment->Attribute('resulttype'))))
                    {
                        case 'attainment':
                            $attainment[] = (int)$grade->Attribute('value');
                            $resulttype = 'attainment';
                            break;
                        case 'targets':
                        case 'target':
                        case 'predicted': 
                            $targets[] = (int)$grade->Attribute('value');
                            $resulttype = 'targets';
                            break;
                        
                    }
                        
                    $sets = $gradesets->xpath("//gradeset [@refid='".$assessment->Attribute('gradeset')."']");
    
                    foreach($sets as $gradeset => $gradeitems)
                    {
                        foreach($gradeitems as $gradeitem)
                        {
                            $gradelables[(int)$gradeitem->Attribute('value')] = $gradeitem->Attribute('title');
                        }
                    }
                    
                                  
                    $gradetypestrings[$resulttype] =  $assessment->Attribute('resulttype');
                    
                    $data[$assessment->Attribute('session')][strtolower(str_replace(' ','',$assessment->Attribute('subject')))]['category'] = $sessions[$assessment->Attribute('session')];
                    $data[$assessment->Attribute('session')][strtolower(str_replace(' ','',$assessment->Attribute('subject')))][$resulttype] = (int)$grade->Attribute('value');

                    if($max < (int)$grade->Attribute('value'))
                        $max = (int)$grade->Attribute('value');
                    
                    if($min > (int)$grade->Attribute('value') || $min == 0)
                        $min = (int)$grade->Attribute('value'); 
                    
                 
                }
                $count++;
            }
        }

        if(empty($data))
        {
            return '';
        }
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_guardian_view_assessment_overview_chart',
                        'fullpath'  =>  '/local/zilink/plugins/guardian/view/interfaces/default/module.js',
                        'requires'  =>  array('base', 'node', 'io','charts','json'),
                        'strings' => array ());
        
        
        
        $chartdata = array();
        $lastsession = '';
        foreach($sessionOrder as $index)
        {
            if(isset($data[$index])) 
            {
                foreach($data[$index] as $subjects )
                {
                    if(array_key_exists('attainment', $subjects) || array_key_exists('targets', $subjects))
                    {
                        $chartdata[] = $subjects;
                    }
                }
            }
            
        }

        $max += 6;
        $min -= 6;

        $jsdata = array($chartdata,$min, $max,$gradelables, $gradetypestrings,$links, sesskey());

        $PAGE->requires->js_module($jsmodule);
                                         
        $PAGE->requires->js_init_call('M.local_zilink_guardian_view_assessment_overview_chart.init', $jsdata, false, $jsmodule);
        
        $cells = array_merge(array('<div id="zilink_guardian_view_assessment_overview_chart" class="zilink_chart"></div>'),array($this->GetAssessmentSubjectComments($attainment,$targets)));

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array('Assessment','Information');
        $table->align       = array('left', 'left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->data = array_chunk($cells, 2);
        $content = html_writer::table($table);  
        
        return $content;
    }
    
    function GetAssessmentSubjectComments($attainments,$targets)
    {
        global $CFG;
        
        $monitor = array();
        $monitor['below'] = 0;
        $monitor['level'] = 0;
        $monitor['above'] = 0;
        
        foreach($attainments as $index => $attainment)
        {
            if($attainment < $targets[$index])
                $monitor['below']++;
            elseif($attainment == $targets[$index])
                $monitor['level']++; 
            elseif($attainment > $targets[$index])
                $monitor['above']++;    
        }
        
        
        
        $content = $CFG->zilink_guardian_view_default_assessment_subjects_general_comment;
        if(isset($CFG->zilink_guardian_view_default_assessment_subjects_below_trigger) && isset($CFG->zilink_guardian_view_default_assessment_subjects_below_comment))
        {
            if(($monitor['below'] >= $CFG->zilink_guardian_view_default_assessment_subjects_below_trigger) && ($CFG->zilink_guardian_view_default_assessment_subjects_below_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_guardian_view_default_assessment_subjects_below_comment;
        }
        if(isset($CFG->zilink_guardian_view_default_assessment_subjects_level_trigger) && isset($CFG->zilink_guardian_view_default_assessment_subjects_level_comment))
        {   
            if(($monitor['level'] >= $CFG->zilink_guardian_view_default_assessment_subjects_level_trigger) && ($CFG->zilink_guardian_view_default_assessment_subjects_level_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_guardian_view_default_assessment_subjects_level_comment;
        }
        if(isset($CFG->zilink_guardian_view_default_assessment_subjects_above_trigger) && isset($CFG->zilink_guardian_view_default_assessment_subjects_above_comment))
        {   
            if(($monitor['above'] >= $CFG->zilink_guardian_view_default_assessment_subjects_above_trigger) && ($CFG->zilink_guardian_view_default_assessment_subjects_above_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_guardian_view_default_assessment_subjects_above_comment;
        }
        
        $content = str_replace('[[FIRSTNAME]]', $this->child->user->firstname, $content);
        return $content;
    }

    private function GetAssessmentOverviewGraph()
    {
        global $CFG,$DB,$PAGE;
        
        $data = array();

        $gradesets = $this->data->GetGlobalData('assessment_gradesets');
         
        $count = 1;

        if(!isset($this->child->assessment->assessmenthistory->assessments->assessment))
            return '';
            
        if(!isset($gradesets->gradesets))
            return '';
        
        $max = array();
        $min = array();
        
        $gradelables = array();
        $gradetypestrings = array();
        $links = array();
        $attainment = array();
        $targets = array();
        
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
        
        $sessionOrder = explode(',', $CFG->zilink_data_manager_sessions_order);

        foreach($this->child->assessment->assessmenthistory->assessments->assessment as $assessment)
        {

            if($assessment->Attribute('type') == 'Grade' && array_key_exists($assessment->Attribute('session'),$sessions))
            {
            
                $result = $assessment->Attribute('result');
                $refid = $assessment->Attribute('gradeset');
                
                $grade = new stdClass();
                $gradesets = @simplexml_load_string($gradesets->asXML(),'simple_xml_extended');
                $grade = $gradesets->xpath("//gradeset[@refid='".$assessment->Attribute('gradeset')."']/grade[@title='".$result."']");
                $grade = $grade[0];
                
                if(!is_object($grade))
                {
                    return '';
                }
                
                $resulttype = '';
                switch(strtolower(str_replace(' ','',$assessment->Attribute('resulttype'))))
                {
                    
                    case 'attainment':
                        $attainment[] = (int)$grade->Attribute('value');
                        $resulttype = 'attainment';
                        break;
                    case 'targets':
                    case 'target':
                    case 'predicted': 
                        $targets[] = (int)$grade->Attribute('value');
                        $resulttype = 'targets';
                        break;
                    
                }
                
                $sets = $gradesets->xpath("//gradeset [@refid='".$assessment->Attribute('gradeset')."']");

                foreach($sets as $gradeset => $gradeitems)
                {
                    
                    foreach($gradeitems as $gradeitem)
                    {
                        $gradelables[(int)$gradeitem->Attribute('value')] = $gradeitem->Attribute('title');
                    }
                }
                
                if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects'))
                {
                    require_once($CFG->dirroot.'/course/lib.php');
                    $courses = enrol_get_users_courses($this->child->user->id);
                    
                    foreach($courses as $course)
                    {
                        $category = $DB->get_record('course_categories',array('id' => $course->category));
                        
                        if(isset($category->ctxpath))
                        {
                            $path = explode('/',$category->ctxpath);
                        } else {
                            $path = explode('/',$category->path);
                        }

                        if(($CFG->zilink_category_root == 0) || in_array($CFG->zilink_category_root,$path))
                        { 
                            
                            $allowed_subjects = zilinkdeserialise($CFG->zilink_guardian_view_default_subjects_allowed);
                            
                            $top_category = $DB->get_record('course_categories',array('id' => $path[1]));
                            
                            if(isset($allowed_subjects[$top_category->id]) && $allowed_subjects[$top_category->id] == 1 && $top_category->visible == 1 && $assessment->Attribute('subject') == $top_category->name)
                            {
                                    $links[strtolower(str_replace(' ','',$assessment->Attribute('subject')))] = $this->httpswwwroot.'/local/zilink/plugins/guardian/view/interfaces/default/pages/subjects.php?cid='.$this->course->id.'&offset='.$this->offset.'&sesskey='.sesskey().'&id='.$top_category->id;
                            }                   
                        }
                    }       
                }               
                $gradetypestrings[$resulttype] =  $assessment->Attribute('resulttype');
                
                $data[$assessment->Attribute('session')][strtolower(str_replace(' ','',$assessment->Attribute('subject')))]['category'] = $assessment->Attribute('subject');
                $data[$assessment->Attribute('session')][strtolower(str_replace(' ','',$assessment->Attribute('subject')))][$resulttype] = (int)$grade->Attribute('value');

                if(!isset($max[strval($assessment->Attribute('session'))]) || $max[strval($assessment->Attribute('session'))] < (int)$grade->Attribute('value'))
                    $max[strval($assessment->Attribute('session'))] = (int)$grade->Attribute('value');
                
                if(!isset($min[strval($assessment->Attribute('session'))]) ||$min[strval($assessment->Attribute('session'))] > (int)$grade->Attribute('value') || $min == 0)
                    $min[strval($assessment->Attribute('session'))] = (int)$grade->Attribute('value'); 
             
            }
            $count++;
        }

        if(empty($data))
        {
            return '';
        }
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_guardian_view_assessment_overview_chart',
                        'fullpath'  =>  '/local/zilink/plugins/guardian/view/interfaces/default/module.js',
                        'requires'  =>  array('base', 'node', 'io','charts','json'),
                        'strings' => array ());

        $chartdata = array();
        $lastsession = '';
        foreach($sessionOrder as $index)
        {
            if(isset($data[$index])) 
            {
                
                foreach($data[$index] as $subjects )
                {
                    if(array_key_exists('attainment', $subjects))
                    {
                        $lastsession = $index;
                        $minimum = $min[$index];
                        $maximum = $max[$index];
                        $chartdata[$index][] = $subjects;
                    }
                }
            }
            
        }
        $chartdata = $chartdata[$lastsession];

        $maximum += 6;
        $minimum -= 6;

        $jsdata = array($chartdata,$minimum, $maximum,$gradelables, $gradetypestrings,$links, sesskey());

        $PAGE->requires->js_module($jsmodule);
                                         
        $PAGE->requires->js_init_call('M.local_zilink_guardian_view_assessment_overview_chart.init', $jsdata, false, $jsmodule);
        
        $cells = array_merge(array('<div id="zilink_guardian_view_assessment_overview_chart" class="zilink_chart"></div>'),array($this->GetAssessmentOverviewComments($attainment,$targets)));

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array('Assessment','Information');
        $table->align       = array('left', 'left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->data = array_chunk($cells, 2);
        $content = html_writer::table($table);  
        
        return $content;
    }

    
    private function GetAssessmentOverviewComments($attainments,$targets)
    {
        global $CFG;
        
        $monitor = array();
        $monitor['below'] = 0;
        $monitor['level'] = 0;
        $monitor['above'] = 0;
        
        foreach($attainments as $index => $attainment)
        {
            if(isset($targets[$index]))
            {
                if($attainment < $targets[$index])
                    $monitor['below']++;
                elseif($attainment == $targets[$index])
                    $monitor['level']++; 
                elseif($attainment > $targets[$index])
                    $monitor['above']++;  
            }  
        }
        
        
        $content = $CFG->zilink_guardian_view_default_assessment_overview_general_comment;
        if(isset($CFG->zilink_guardian_view_default_assessment_overview_below_trigger) && isset($CFG->zilink_guardian_view_default_assessment_overview_below_comment))
        {
            if(($monitor['below'] >= $CFG->zilink_guardian_view_default_assessment_overview_below_trigger) && ($CFG->zilink_guardian_view_default_assessment_overview_below_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_guardian_view_default_assessment_overview_below_comment;
        }
        if(isset($CFG->zilink_guardian_view_default_assessment_overview_level_trigger) && isset($CFG->zilink_guardian_view_default_assessment_overview_level_comment))
        {   
            if(($monitor['level'] >= $CFG->zilink_guardian_view_default_assessment_overview_level_trigger) && ($CFG->zilink_guardian_view_default_assessment_overview_level_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_guardian_view_default_assessment_overview_level_comment;
        }
        if(isset($CFG->zilink_guardian_view_default_assessment_overview_above_trigger) && isset($CFG->zilink_guardian_view_default_assessment_overview_above_comment))
        {   
            if(($monitor['above'] >= $CFG->zilink_guardian_view_default_assessment_overview_above_trigger) && ($CFG->zilink_guardian_view_default_assessment_overview_above_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_guardian_view_default_assessment_overview_above_comment;
        }
        
        $content = str_replace('[[FIRSTNAME]]', $this->child->user->firstname, $content);
        return $content;
    }
    
    private function GetAttendanceOverviewGraph()
    {
        
        global $CFG,$PAGE;
        
        if($this->child->attendance == null)
        {
            
            $table              = new html_table();
            $table->cellpadding = '10px';    
            $table->width       = '68%';
            $table->head        = array('Attendance Information');
            $table->align       = array('left');
            $table->border      = '2px'; 
            $table->tablealign  = 'center';
            
            $table->data = array(array('Attendance information is currently being process for published. Please check back later'));
            
            return html_writer::table($table);  
        }
        
        $colours = array(   'present'               => '#006600',
                            'late'                  => '#FF9900',
                            'authorisedabsence'     => '#6666FF',
                            'unauthorisedabsence'   => '#FF0000'
                        );
                        
        $xlabels = array();
        $values = array();
        
        $dataString = '';
        $data = array();
        
        $count = 1;
        
        foreach($this->child->attendance->attendance->terms->term as $term)
        {
            $tmp = array();
            $dataString .= '{ ';
            foreach($term->code as $code)
            {
                if($code->Attribute('type') <> 'NA')
                {
                    if($code->Attribute('type') == 'Present')
                    {
                        $color = $colours['present'];
                        $tmp[1]['present'] = (!isset($tmp[1]['present'])) ? (int)$code->Attribute('value') : $tmp[1]['present']+ $code->Attribute('value');
                        
                    }
                    if($code->Attribute('type') == 'Late')
                    {
                        $color = $colours['late'];
                        $tmp[2]['late'] = (!isset($tmp[2]['late'])) ? (int)$code->Attribute('value') : $tmp[2]['late'] + $code->Attribute('value');
                    }
                    if($code->Attribute('type') == 'Absent')
                    {
                        if($code->Attribute('status') == 'Authorised') {
                            $tmp[3]['authorisedabsence'] = (!isset($tmp[3]['authorisedabsence'])) ? (int)$code->Attribute('value') : $tmp[3]['authorisedabsence'] + $code->Attribute('value');
                        } else {
                            $tmp[4]['unauthorisedabsence'] = (!isset($tmp[4]['unauthorisedabsence'])) ? (int)$code->Attribute('value') : $tmp[4]['unauthorisedabsence'] +$code->Attribute('value');
                        }
                    }
                }
            }
            $tmp[1]['present'] = (isset($tmp[1]['present'])) ? ($tmp[1]['present'] /2) : 0;
            $tmp[2]['late'] = (isset($tmp[2]['late'])) ? ($tmp[2]['late'] /2) : 0;
            $tmp[3]['authorisedabsence'] = (isset($tmp[3]['authorisedabsence'])) ? ( $tmp[3]['authorisedabsence'] /2) : 0;
            $tmp[4]['unauthorisedabsence'] = (isset($tmp[4]['unauthorisedabsence'])) ? ( $tmp[4]['unauthorisedabsence'] / 2) : 0;
            
            ksort($tmp);
            $record = array();
            
//          $dataString .= 'category:"Term '.$count.'", ';
            $record['category'] = 'Term '.$count;
            foreach($tmp as $items)
            {
                foreach($items as $type => $value)
                {
                    $record[$type] = $value;
//                  $dataString .= $type.':'.$value.', ';
                    $record[$type] = $value;
                    //$legends[] = new bar_stack_key( $colours[$type], get_string($type,'block_zilink'), 13 );
                    //$section =  new bar_stack_value($value, $colours[$type]);
                    //$stack[] = $section;
                    $values[] = $value;
                }
            }
            
//          $dataString = substr($dataString, 0, -2).' }, ';
//          $xlabels[] = 'Term '.$count;
            $count++;
            
            $data[] = $record;
        }
//      $dataString = '['.substr($dataString, 0, -2).']';
        

//      $ylabels = array("0");
//      for($i = 1; $i <array_sum($values)+10;$i++)
//      {
//          if($i % 5)
//              $ylabels[] = '';
//          else 
//              $ylabels[] = ($i /5);
//      }
    
        $cells = array_merge(array('<div class="rotate">Weeks</div><div id="zilink_guardian_view_attendance_overview_chart" class="zilink_chart">'),array($this->GetAttendanceGraphComments($tmp)));

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array('Attendance','Information');
        $table->align       = array('left', 'left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->data = array_chunk($cells, 2);
        $content = html_writer::table($table);  
        
        $max = 0;
        foreach ( $data as $item)
        {
            if(array_sum($item) > $max)
                $max = array_sum($item);
        }
        
        $max = ceil($max / 5) * 5;
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_guardian_view_attendance_overview_chart',
                        'fullpath'  =>  '/local/zilink/plugins/guardian/view/interfaces/default/module.js',
                        'requires'  =>  array('base', 'node', 'io','charts','json'),
                        'strings' => array (    array('present', 'local_zilink'),
                                            array('authorisedabsence', 'local_zilink'),
                                            array('unauthorisedabsence', 'local_zilink'),
                                            array('late', 'local_zilink')
                                    )); 

        $jsdata = array($data,$max,sesskey());

        $PAGE->requires->js_module($jsmodule);
        $PAGE->requires->js_init_call('M.local_zilink_guardian_view_attendance_overview_chart.init', $jsdata, false, $jsmodule);
        
        return $content;
    }
    
    
    private function GetAttendanceGraphComments($data)
    {
        global $CFG;
        
        $monitor = array();
        
        foreach($data as $type)
        {
            foreach($type as $index => $value)
            {
                if($index== 'authorisedabsence')
                {
                    $index = 'authorised_absence';
                }    
                
                if($index== 'unauthorisedabsence')
                {
                    $index = 'unauthorised_absence';
                } 
                if(!isset($monitor[$index]))
                {
                    $monitor[$index]['below'] = 0;
                    $monitor[$index]['above'] = 0;
                }
               
                if(isset($CFG->{'zilink_guardian_view_default_attendance_overview_'.$index.'_below_trigger'}))
                {
                    if($value < (int)$CFG->{'zilink_guardian_view_default_attendance_overview_'.$index.'_below_trigger'})
                        $monitor[$index]['below']++;
                }
                
                if(isset($CFG->{'zilink_guardian_view_default_attendance_overview_'.$index.'_above_trigger'}))
                {   
                    if($value > (int)$CFG->{'zilink_guardian_view_default_attendance_overview_'.$index.'_above_trigger'})
                        $monitor[$index]['above']++;
                }
            }
        }
        
        $content = $CFG->zilink_guardian_view_default_attendance_overview_general_comment;
        
        foreach($monitor as $type => $item)
        {
            
            foreach($item as $index => $value)
            {
                if($index == 'below' && $value > 0)
                {
                    
                    if(
                     isset($CFG->{'zilink_guardian_view_default_attendance_overview_'.$type.'_'.$index.'_comment'}))
                    {
                            $content .= '<br><br>'.$CFG->{'zilink_guardian_view_default_attendance_overview_'.$type.'_'.$index.'_comment'};
                    }
                }
                if($index == 'above' && $value > 0)
                {
                    if(isset($CFG->{'zilink_guardian_view_default_attendance_overview_'.$type.'_'.$index.'_comment'}))
                    {
                            $content .= '<br><br>'.$CFG->{'zilink_guardian_view_default_attendance_overview_'.$type.'_'.$index.'_comment'};
                    }
                }
            }
        }
        
        $content = str_replace('[[FIRSTNAME]]', $this->child->user->firstname, $content);
        return $content;
    }
    
    function GetStudentPhotoPanels()
    {

        if(($this->person->Security()->IsAllowed('local/zilink:guardian_view_student_details_photo')) && (is_object($this->child->picture) && $this->child->picture <> null && @is_object($this->child->picture->picture))) {
            
            $pic = (string)$this->child->picture->picture->src;
            if(!empty($pic))
            {
                $panel = new ZiLinkPanel();
                $panel->SetTitle(get_string('guardian_view_default_student_photo','local_zilink'));
        
                $cells = array();
                
                $cells[] = array('<img src="data:image/png;base64,'.$pic.' "/>');
               
                $panel->SetWidth('auto');
                $panel->SetCSS('left zilink_guardian_view_table_small_left');
                $panel->SetContent($cells);
                
                return $panel->Display();
            }
        }
        else
        {
            return '';
        }

    }
    
    function GetStudentDetailPanels()
    {

        $panel = new ZiLinkPanel();
        $panel->SetTitle(get_string('guardian_view_default_student_details','local_zilink',$this->child->user->firstname));
        
        $cells = array();
    
        $cells[] = array(get_string('guardian_view_default_student_name','local_zilink'),utf8_decode($this->child->extended_details->PersonalInformation->Name->GivenName .' '.$this->child->extended_details->PersonalInformation->Name->FamilyName));
        $cells[] = array(get_string('guardian_view_default_student_dob','local_zilink'),date('d/m/Y',strtotime($this->child->extended_details->PersonalInformation->Demographics->BirthDate)));
        $cells[] = array(get_string('guardian_view_default_student_gender','local_zilink'),$this->child->extended_details->PersonalInformation->Demographics->Gender);
        $cells[] = array(get_string('guardian_view_default_student_house','local_zilink'),($this->child->details->person->schoolregistration->attribute('house') == 'UNKNOWN') ? '-' : $this->child->details->person->schoolregistration->attribute('house'));
        $cells[] = array(get_string('guardian_view_default_student_year_group','local_zilink'),($this->child->details->person->schoolregistration->attribute('year')  == 'UNKNOWN') ? '-' : $this->child->details->person->schoolregistration->attribute('year'));
        $cells[] = array(get_string('guardian_view_default_student_registration_group','local_zilink'),($this->child->details->person->schoolregistration->attribute('registration')  == 'UNKNOWN') ? '-' : $this->child->details->person->schoolregistration->attribute('registration'));
       
        $panel->SetWidth('40%');
        $panel->SetCSS('left zilink_guardian_view_table_small_left');
        $panel->SetContent($cells);
            
        return $panel->Display();

    }

    private function GetTodaysTimetable()
    {
        
        $panel = new ZiLinkPanel();
        $timetable = new ZiLinkTimetable(1);

        if(date('N') > 5)
        {
            $panel->SetTitle(get_string('guardian_view_default_mondays_timetable','local_zilink'));
        }
        else
        {
            $panel->SetTitle(get_string('guardian_view_default_todays_timetable','local_zilink'));
                
        } 
 
        $panel->SetContent(array_chunk(array($timetable->GetTodaysTimetable(array('user_idnumber' => $this->child->user->idnumber))),1));
        $panel->SetWidth('40%');
        $panel->SetCSS('right zilink_guardian_view_table_small_right');
        
        return $panel->Display();

    }

    private function GetStudentAttendanceSummary()
    {

        if($this->child->attendance == null)
            return '';
            
        global $OUTPUT;
        
        $content = '';
        $panel = new ZiLinkPanel();
        $panel->SetTitle(get_string('guardian_view_default_recent_attendance','local_zilink'));
        $panel->SetWidth('100%');
        $panel->SetCSS('generaltable boxaligncenter zilink_table_width');

        $row = array();
        
        $firstweek="";
        $secondweek="";
        
        $end = strtotime($this->child->attendance->attendance->snapshot->Attribute('end'));
        /*
        if($end ==  $this->geteffectivedate()) 
        {
            $firstweek = date('d/m/Y',strtotime('-1 Week',strtotime('this monday',$end)));
            $secondweek = date('d/m/Y',strtotime('previous monday',$end));
        } 
        else if($end <  $this->geteffectivedate() && date('N', $this->geteffectivedate()) == 1)
        {
            $firstweek = date('d/m/Y',strtotime('-1 Week',$this->geteffectivedate()));
            $secondweek = date('d/m/Y',$this->geteffectivedate());
        }
        else if  ($end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) < 5)
        {
            $firstweek = date('d/m/Y',strtotime('-1 Week',strtotime('this monday',$this->geteffectivedate())));
            $secondweek = date('d/m/Y',strtotime('this monday',$this->geteffectivedate()));
        }
        else if($end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) > 5 ) 
        {
            $firstweek = date('d/m/Y',strtotime("previous Monday",$this->geteffectivedate()));
            $secondweek = date('d/m/Y',strtotime("this Monday",$this->geteffectivedate()));
        }
        */
        
        if($end ==  $this->geteffectivedate())
        {
            if(date('N', $this->geteffectivedate()) < 5)
            {
                $firstweek = date('d/m/Y',strtotime('-1 Week',strtotime('monday this week',$end)));
                $secondweek = date('d/m/Y',strtotime('monday last week',$end));
            } else {
                $firstweek = date('d/m/Y',strtotime("monday this week",$this->geteffectivedate()));
                $secondweek = date('d/m/Y',strtotime("monday next week",$this->geteffectivedate()));
            }
        }
        else if($end <  $this->geteffectivedate() && date('N', $this->geteffectivedate()) == 1)
        {
            $firstweek = date('d/m/Y',strtotime('-1 Week',$this->geteffectivedate()));
            $secondweek = date('d/m/Y',$this->geteffectivedate());
        }
        else if  ($end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) < 5)
        {
            $firstweek = date('d/m/Y',strtotime('monday last week',$this->geteffectivedate()));
            $secondweek = date('d/m/Y',strtotime('monday this week',$this->geteffectivedate()));
        }
        else if($end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) > 5 )
        {
            $firstweek = date('d/m/Y',strtotime("monday this week",$this->geteffectivedate()));
            $secondweek = date('d/m/Y',strtotime("monday next week",$this->geteffectivedate()));
        }
        
        
        $days = array(date('d/m/Y',strtotime("-1 Week last Monday",$this->geteffectivedate())),'Monday','Tuesday','Wednesday','Thursday','Friday');   
        $marks = $this->GetStudentAttendanceMarkImages();
    
        $cells = array_merge(array('AM'),$marks[1]['am'],array('AM'),$marks[2]['am'],array('PM'),$marks[1]['pm'],array('PM'),$marks[2]['pm']);

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '100%';
        $table->head        = array('Week Begining - '.$firstweek,'Monday','Tuesday','Wednesday','Thursday','Friday','Week Begining - '.$secondweek,'Monday','Tuesday','Wednesday','Thursday','Friday');
        $table->align       = array('center', 'center', 'center', 'center','center','center','center', 'center', 'center', 'center','center','center');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        $table->attributes['class'] = 'generaltable zilink_guardian_view_table';
        
        $table->data = array_chunk($cells, 12);
        $content .= html_writer::table($table);         
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '100%';
        $table->head        = array('Legend');
        $table->headspan    = array(6);
        $table->align       = array('center','center','center','center','center','center');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';

        $legend = array(        get_string('present','local_zilink'),
                                get_string('late','local_zilink'),
                                get_string('authorisedabsence','local_zilink'),
                                get_string('unauthorisedabsence','local_zilink'),
                                get_string('schoolsclosed','local_zilink'),
                                get_string('awaitpublication','local_zilink'),
                                $OUTPUT->pix_icon('guardian/view/interfaces/default/button-green', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')),
                                $OUTPUT->pix_icon('guardian/view/interfaces/default/button-purple', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('guardian/view/interfaces/default/button-blue', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('guardian/view/interfaces/default/button-red', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('guardian/view/interfaces/default/dialog-declare', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('guardian/view/interfaces/default/appointment-soon', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'))); 
                                        
                                
        $cells = array_merge($legend);
        $table->data = array_chunk($cells, 6);
            
        $content .= html_writer::table($table);
        $row[] = $content;
    
        $panel->SetContent(array_chunk($row, 1));
        
        return $panel->Display();
    }

    public function GetStudentAttendanceMarkImages($marksonly = false)
    {
        $data = $this->AdjustAttendanceMarks();
        $marks = array();
        $week = 1;
        for($i = 1; $i <= 28; $i++)
        {
            
            if($i % 2)
            {
                if(($i < 11 || $i > 14 ) && ($i < 25 || $i > 28 ))
                {
                    $marks[$week]['am'][] = substr($data, $i-1,1);
                }
            }       
            else
            {
                if(($i < 11 || $i > 14 ) && ($i < 25 || $i > 28 ))
                {
                    $marks[$week]['pm'][] = substr($data, $i-1,1);
                }
            }
            if($i == 14)
                $week++;
            
        }
        if($marksonly){
            return $marks;
        }
        return $this->GetStudentAttendanceMarkIcons($marks);
    }
    
    public function GetStudentAttendanceMarkIcons($marks)
    {
        global $CFG, $OUTPUT;
        
        $icons = array();
        $count = array();
        
        $end =  strtotime($this->child->attendance->attendance->snapshot->Attribute('end'));
        
        if($end ==  $this->geteffectivedate() || $end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) <= 5) {
            $delay = $CFG->zilink_guardian_view_default_attendance_overview_delay + round((strtotime('this friday',$this->geteffectivedate()) - $this->geteffectivedate()) / (60 * 60 * 24),0);
        } else if($end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) > 5 ) {
            $delay = $CFG->zilink_guardian_view_default_attendance_overview_delay + round((strtotime('last friday',$this->geteffectivedate()) - $this->geteffectivedate()) / (60 * 60 * 24),0);
        } else {
            $delay = 0;
        }
        
        foreach ($marks as $week => $sessions) {
            foreach ($sessions as $session => $marks) {
            
                if (!isset($count[$session])) {
                    $count[$session] = 0;
                }
                    
                foreach($marks as $index => $mark)
                {
                    if(((10 - $count[$session]) <= $delay) && !$this->isCurrentWeekHoliday())
                    {
                        $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/appointment-soon', '', 'local_zilink');
                    }
                    else
                    {
                        switch(ord($mark))
                        {
                            case ord("#"):
                            case ord("Y"):
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/dialog-declare', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            case ord("/") :
                            case 92: // "\\":
                            case ord("B"):
                            case ord("D"):
                            case ord("B"):
                            case ord("V"):
                            case ord("W"):
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/button-green', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            case ord("C"):
                            case ord("E"):
                            case ord("F"):
                            case ord("H"):
                            case ord("I"):
                            case ord("J"):
                            case ord("M"):
                            case ord("P"): 
                            case ord("R"): 
                            case ord("S"): 
                            case ord("T"):                     
                                  $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/button-blue', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                  break;
                            case ord("G"):
                            case ord("N"):
                            case ord("O"):
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/button-red', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            case ord("L"):
                            case ord("U"):
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/button-purple', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            default:
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('guardian/view/interfaces/default/appointment-soon', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                        }        
                    }
                    $count[$session]++;
                }
            }
        }
        return $icons;
    }

    public function AdjustAttendanceMarks()
    {
        date_default_timezone_set('UTC');
        
        $marks = $this->child->attendance->attendance->snapshot->Attribute('marks');

        $start = strtotime($this->child->attendance->attendance->snapshot->Attribute('start'));
        $end =  strtotime($this->child->attendance->attendance->snapshot->Attribute('end'));
        
        $drift = 0;
        if (($end <  $this->geteffectivedate()))
        {
            $drift = round(($this->geteffectivedate() - $end) / (60 * 60 * 24),0);
        }

        if ($drift > 0 )
        {
            $marks .= str_repeat('-',$drift*2);
        }
        
        if($end ==  $this->geteffectivedate() || $end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) <= 5) {
            $days = round((strtotime('this friday',$this->geteffectivedate()) - $this->geteffectivedate()) / (60 * 60 * 24));
            $days = 2 + $days;
        } else if($end < $this->geteffectivedate() && date('N', $this->geteffectivedate()) > 5 ) {
            $days = round((strtotime('last friday',$this->geteffectivedate()) - $this->geteffectivedate()) / (60 * 60 * 24));
            $days = 2 + $days;
        } else {
            $days = 0;
        }
        
        if($days > 0)
        {
            if($this->IsCurrentWeekHoliday())
                $marks .= str_repeat('#',$days*2);
            else
                $marks .= str_repeat('-',$days*2);
        }

        $marks = substr($marks, -28);
        return $marks;   
    }


    function GetSubjectSubmittedWork($id)
    {
        
        
        global $DB,$CFG;
        
        require_once($CFG->libdir . '/coursecatlib.php');
        
        $student_course_list = array();
    
        $parent_category = $DB->get_record('course_categories',array('id' => $id));
        
        if(is_object($parent_category))
        {
            $cat = coursecat::get($parent_category->id);
            $categories = $cat->get_children();
            $categories[] = $parent_category;   
            
            $my_student_courses = enrol_get_users_courses($this->child->user->id);
            
            foreach($my_student_courses as $my_student_course)
                $my_student_courses_list[]  = $my_student_course->shortname;
            
            foreach ($categories as $category)
            {
                $courses =  get_courses($category->id);
                foreach($courses as $course)
                {
                    if(in_array($course->shortname,$my_student_courses_list))
                        $student_course_list[] = $course->id;
                }
            }
            
            $grades_data = null;
            
            if(!empty($student_course_list))
            {
                
                
                
                $grades_data = $DB->get_records_sql("   SELECT  {grade_items}.id,
                                                            {grade_grades}.userid,
                                                            {grade_items}.courseid,
                                                            {grade_items}.itemmodule as type, 
                                                            {grade_items}.itemname as name, 
                                                            {grade_grades}.finalgrade as grade, 
                                                            {grade_grades}.rawgrademax as max,
                                                            {grade_grades}.feedback,
                                                            {grade_grades}.timecreated,
                                                            {grade_grades}.timemodified
                                                    FROM    {grade_grades}, {grade_items} 
                                                    WHERE   {grade_grades}.itemid = {grade_items}.id 
                                                    AND     {grade_grades}.rawgrade IS NOT NULL
                                                    AND     courseid IN (".implode(',',$student_course_list) .")
                                                    AND     userid = ".$this->child->user->id ."
                                                    ORDER BY type, timecreated, timemodified");
            }
    
            if(empty($grades_data))
                return '';
                
            foreach ($grades_data as $grade_data)
            {
                $time = ($grade_data->timecreated == null) ? $grade_data->timemodified : $grade_data->timecreated;
                $grades[$grade_data->type][] = array(   'grade' => (int)$grade_data->grade/(int)$grade_data->max  * 100 . '%',
                                                        'name'  => $grade_data->name,
                                                        'date' => date('d/m/Y',$time),
                                                        'feedback' => $grade_data->feedback);
            }
                                        
            $table              = new html_table();
            $table->cellpadding = '10px';    
            $table->width       = '68%';
            
            $table->head        = array(get_string('guardian_view_default_submitted_work_module_type','local_zilink'),get_string('date'),get_string('description'),get_string('grade'),get_string('guardian_view_default_submitted_work_comment','local_zilink')); 
            $table->align       = array('left','left','left','left');
            
            $table->border      = '2px'; 
            $table->tablealign  = 'center';
            
            $cells = array();
            
            foreach($grades as $key => $items)
            {
                foreach ($items as $item) {
                    $cells[] = get_string($key,'block_progress');
                    $cells[] = $item['date'];
                    $cells[] = $item['grade'];
                    $cells[] = $item['name'];
                    $cells[] = empty($item['feedback']) ? 'None' : $item['feedback'];
                }
            }
    
            $table->data = array_chunk($cells, 5);
            $content = html_writer::table($table);
            return $content;
        }
    }

    private function GetSubjectTeachers($id)
    {
        global $DB;
        
        $cohorts = array();
        $cells = array();
        
        $courses = enrol_get_users_courses($this->child->user->id);
        
        foreach($courses as $course)
        {
            $mdl_category = $DB->get_record('course_categories',array('id' => $course->category));
            
            if(isset($mdl_category->ctxpath))
            {
                $categories = explode('/',$mdl_category->ctxpath);
            } else {
                $categories = explode('/',$mdl_category->path);
            }

            foreach($categories as $category)
            {
                if($category == $id || ($id == 0 && is_numeric($category)))
                {
                    $enrolinstances = enrol_get_instances($course->id,true);
                    $enrolments = $DB->get_records('enrol',array('courseid' => $course->id, 'roleid' => 5, 'enrol'=> 'zilink_cohort' ));
                    foreach ($enrolments as $enrolment)
                    {
                        if($DB->record_exists('cohort_members',array('userid' => $this->child->user->id,'cohortid' =>$enrolment->customint1)))
                        {
                            if (!in_array($enrolment->customint1,$cohorts)) {
                                $cat = $DB->get_record('course_categories',array('id' =>  $category));
                                if (is_object($cat)) {
                                    $cohorts[$cat->name][] = $enrolment->customint1;
                                }
                            }
                        }
                    }
                }
            }
        }
        
        $teacherlist = array();
        ksort($cohorts);
        foreach($cohorts as $subject => $cs)
        {
            foreach($cs as $cohort) {
                $teachers = $DB->get_records('zilink_cohort_teachers',array('cohortid' => $cohort));
                foreach($teachers as $teacher)
                {
                    $user = $DB->get_record('user',array('id' => $teacher->userid, 'deleted' => 0));
                    
                    if(is_object($user)) {
                        if(!isset($teacherlist[$subject]) || !in_array(fullname($user),$teacherlist[$subject])) {
                            
                            if($id == 0) {
                                $cells[] = $subject;
                            }
                            $cells[] = fullname($user);
                            if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_teacher_details_email')) {
                                if(strpos($user->email, '@') !== false) {
                                    $cells[] = $user->email;
                                } else {
                                    $cells[] = '';
                                }
                            }
                            $teacherlist[$subject][] = fullname($user);
                        }
                    }
                }
            }
        }

        if(empty($cells))
            return '';
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '48%';
        
        $colums = array();
        
        if($id == 0) {
            $colums[] = get_string('guardian_view_default_subjects','local_zilink');
            
        }
        
        $colums[] = get_string('guardian_view_default_teacher','local_zilink').'s';
            
        if($this->person->Security()->IsAllowed('local/zilink:guardian_view_subjects_teacher_details_email'))
        {
            $colums[]  =   get_string('email');
        }
        $table->head        = $colums;
        
        $table->align = array();
        for ($i = 1;  $i <= count($colums); $i++) {
            
            if($i == count($colums)) {
                $table->align[] = 'right';
            } else {
                $table->align[] = 'left';
            }
        }
        
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->data = array_chunk($cells, count($colums));

        return html_writer::table($table);  
    }

    function GetCurrentWorkOverview()
    {
        global $CFG,$DB,$COURSE;
        
        require_once($CFG->dirroot.'/course/lib.php');
        
        if(file_exists($CFG->dirroot.'/blocks/progress/lib.php'))
        {
            require_once($CFG->dirroot.'/blocks/progress/lib.php');
        }
        else {
            return '';
        }
        
        $content = '';

        $courses = enrol_get_users_courses($this->child->user->id);
        
        if(empty($courses))
            return '';  
        
        $student_course_list = array();
        foreach($courses as $course)
        {
            $category = $DB->get_record('course_categories',array('id' => $course->category));
            
            if(!isset($category->ctxpath))
            {
                $tree = explode('/',$category->path);
            }
            else 
            {
                $tree = explode('/',$category->ctxpath);
            }
            
            for($i = 1; $i < count($tree); $i++)
            {
                if($tree[$i] == $CFG->zilink_category_root)
                {
                    $parent_category = $DB->get_record('course_categories',array('id' => $tree[$i+1]));
                    if($course->visible == 1)
                        $student_course_list[$parent_category->id][]  = $course->id;
                }
                elseif($CFG->zilink_category_root == 0 && $i == 1) 
                {
                    $parent_category = $DB->get_record('course_categories',array('id' => $tree[$i]));
                    
                    if($course->visible == 1)
                        $student_course_list[$parent_category->id][]  = $course->id;     
                }
            }
        }

        $work = array();
        if(!empty($student_course_list))
        {
            
            
            $block = new stdClass;

            //$eventArray = array();
            
            $numevents = 0;
            $visibleEvents = 0;
                    
            //if(!empty($student_course_list))
            //{   
                foreach ($student_course_list as $category => $courses)
                {
                    foreach($courses as $course)
                    {
                        try 
                        {
                            $ctmp = $COURSE->id;
                            $COURSE->id = $course;        
                        
                            $modules = block_progress_modules_in_use();
        
                            $eventArray = array();
                            
                            $instances = $DB->get_records_sql("SELECT bi.id, bi.configdata 
                                                 FROM {block_instances} bi
                                                 JOIN {context} c ON bi.parentcontextid = c.id
                                                 WHERE instanceid = ?
                                                 AND bi.blockname = 'progress'", array($course));
                            
                            
                            if (empty($instances)) {
                                    $instances = $DB->get_records('block_instances',array('blockname' => 'progress', 'parentcontextid' => 1, 'showinsubcontexts' =>1));
                            }
                            
                            if (empty($instances)) {
                                continue;
                            }
                            
                            $block->config = array();
                            
                            foreach($instances as $instance)
                            {
                                $now = time();
                                $block->config = unserialize(base64_decode($instance->configdata));
                                if(is_object($block->config)) 
                                {
                                    $events = block_progress_event_information($block->config, $modules);
                                    $attempts = block_progress_attempts($modules, $block->config, $events, $this->child->user->id, $instance->id);
                                    
                                    foreach ($events as $event) {
                                        $attempted = $attempts[$event['type'].$event['id']];
                                        if ($attempted === true) 
                                        {
                                            if($event['type'] == 'quiz')
                                            {
                                                $work[$category]['attempted'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - '.$event['name'];
                                            }
                                            else
                                            {
                                                $work[$category]['attempted'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cmid'].'" target="_blank">'.$event['name'].'</a> ';
                                            }
                                        }
                                        else if (((!isset($block->config->orderby) || $block->config->orderby == 'orderbytime') && $event['expected'] < $now) || ($attempted === 'failed')) 
                                        {
                                            if($event['type'] == 'quiz')
                                            {
                                                $work[$category]['overdue'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - '.$event['name'];
                                            }
                                            else
                                            {
                                                $work[$category]['overdue'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cmid'].'" target="_blank">'.$event['name'].'</a> ';
                                            }
                                        }
                                        else 
                                        {
                                            if($event['type'] == 'quiz')
                                            {
                                                $work[$category]['todo'][]= 'Due '.date('d/m/Y',$event['expected']) . ' - '.$event['name'];
                                            }
                                            else
                                            {
                                                $work[$category]['todo'][]= 'Due '.date('d/m/Y',$event['expected']) . ' - <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cmid'].'" target="_blank">'.$event['name'].'</a> ';
                                            }
                                        }
                                    }
                                }
                                $COURSE->id = $ctmp; 
                            }
                        } 
                        catch (Exception $e){
                            
                        }
                    }
                }    
            //}   
        }           

        $categories = array();
        foreach ($work as $categoryid => $types)
        {
            $category = $DB->get_record('course_categories',array('id' => $categoryid));
            $categories[$categoryid] =  $category->name;
        }
        
        natsort($categories);

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array(get_string('guardian_view_default_current_work','local_zilink',$parent_category->name));
        $table->align       = array('left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $cells = array();
        $cells[] = get_string('guardian_view_default_current_work_desc','local_zilink',$this->child->user);
        
        
        if(!empty($categories))
        {
            foreach($categories as $categoryid => $name)
            {
                $cells[] = html_writer::tag('b',$name);
                
                $cells[] = html_writer::tag('b',get_string('guardian_view_default_current_work_assigned','local_zilink'));
                
                if(!empty($work[$categoryid]['todo']))
                {
                    foreach($work[$categoryid]['todo'] as $bit )
                        $cells[] = html_writer::tag('li', $bit);
                }
                else
                    $cells[] = html_writer::tag('li',get_string('guardian_view_default_current_work_non_assigned','local_zilink'));
                
                $cells[] = html_writer::tag('b',get_string('guardian_view_default_current_work_attempted','local_zilink'));
                                            
                if(!empty($work[$categoryid]['attempted']))
                {
                    foreach($work[$categoryid]['attempted'] as $bit )
                        $cells[] = html_writer::tag('li', $bit);
                }
                else
                    $cells[] = html_writer::tag('li',get_string('guardian_view_default_current_work_non_attempted','local_zilink'));
        
                $cells[] = html_writer::tag('b',get_string('guardian_view_default_current_work_overdue','local_zilink'));
                        
                if(!empty($work[$categoryid]['overdue']))
                {
                    foreach($work[$categoryid]['overdue'] as $bit )
                        $cells[] = html_writer::tag('li', $bit);
                }
                else
                    $cells[] = html_writer::tag('li',get_string('guardian_view_default_current_work_non_overdue','local_zilink'));
            }
        }
        $table->data = array_chunk($cells, 1);
        $content = html_writer::table($table);
        return $content;
        
    } 
    
    function GetSubjectCurrentWork($id)
    {
        global $CFG,$DB,$COURSE;
        
        require_once($CFG->dirroot.'/course/lib.php');
        require_once($CFG->libdir . '/coursecatlib.php');
        
        if(file_exists($CFG->dirroot.'/blocks/progress/lib.php'))
        {
            require_once($CFG->dirroot.'/blocks/progress/lib.php');
        }
        else {
            return '';
        }
        
        $content = '';

        $parent_category = $DB->get_record('course_categories',array('id' => $id));

        if(is_object($parent_category))
        {
            $cat = coursecat::get($parent_category->id);
            $categories = $cat->get_children();
            $categories[] = $parent_category;   
            
            $my_student_courses = enrol_get_users_courses($this->child->user->id);
            
            foreach($my_student_courses as $my_student_course)
                $my_student_courses_list[]  = $my_student_course->shortname;
            
            $student_course_list = array();
            foreach ($categories as $category)
            {
                $courses =  get_courses($category->id);
                    
                foreach($courses as $course)
                {   
                    if(in_array($course->shortname,$my_student_courses_list))
                        $student_course_list[] = $course->id;
                }
            }
            
                       
            $block = new stdClass;

            //$eventArray = array();
            $work = array();
            $numevents = 0;
            $visibleEvents = 0;
            $now = time();
                    
            if(!empty($student_course_list))
            {   
                foreach ($student_course_list as $course)
                {
                    $ctmp = $COURSE->id;
                    $COURSE->id = $course; 
                    $eventArray = array();
                    
                    $modules = block_progress_modules_in_use();   
                    
                    $instances = $DB->get_records_sql('SELECT bi.id, bi.configdata 
                                         FROM {block_instances} bi
                                         JOIN {context} c ON bi.parentcontextid = c.id
                                         WHERE instanceid = ?
                                         AND bi.blockname = "progress"', array($course));
                    
                    if(empty($instances))
                            $instances = $DB->get_records('block_instances',array('blockname' => 'progress', 'parentcontextid' => 1, 'showinsubcontexts' =>1));
                    
                    if(empty($instances))
                        continue;
                    
                    $block->config = array();
                    
                    foreach($instances as $instance)
                    {
                        
                        $block->config = unserialize(base64_decode($instance->configdata));
                        if(is_object($block->config)) 
                        {
                            $events = block_progress_event_information($block->config, $modules);
                            $attempts = block_progress_attempts($modules, $block->config, $events, $this->child->user->id, $instance->id);
                            
                            foreach ($events as $event) {
                                $attempted = $attempts[$event['type'].$event['id']];
                                if ($attempted === true) 
                                {
                                    if($event['type'] == 'quiz')
                                    {
                                        $work['attempted'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - '.$event['name'];
                                    }
                                    else
                                    {
                                        $work['attempted'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cmid'].'" target="_blank">'.$event['name'].'</a> ';
                                    }
                                }
                                else if (((!isset($block->config->orderby) || $block->config->orderby == 'orderbytime') && $event['expected'] < $now) || ($attempted === 'failed')) 
                                {
                                    if($event['type'] == 'quiz')
                                    {
                                        $work['overdue'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - '.$event['name'];
                                    }
                                    else
                                    {
                                        $work['overdue'][] = 'Due '.date('d/m/Y',$event['expected']) . ' - <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cmid'].'" target="_blank">'.$event['name'].'</a> ';
                                    }
                                }
                                else 
                                {
                                    if($event['type'] == 'quiz')
                                    {
                                        $work['todo'][]= 'Due '.date('d/m/Y',$event['expected']) . ' - '.$event['name'];
                                    }
                                    else
                                    {
                                        $work['todo'][]= 'Due '.date('d/m/Y',$event['expected']) . ' - <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cmid'].'" target="_blank">'.$event['name'].'</a> ';
                                    }
                                }
                            }
                        }
                    $COURSE->id = $ctmp;
                    }
                }    
            }   
        }            
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '68%';
        $table->head        = array(get_string('guardian_view_default_current_work','local_zilink',$parent_category->name));
        $table->align       = array('left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $cells = array();
        $cells[] = get_string('guardian_view_default_current_work_desc','local_zilink',$this->child->user);
        $cells[] = html_writer::tag('b',get_string('guardian_view_default_current_work_assigned','local_zilink'));
        
        if(!empty($work['todo']))
        {
            foreach($work['todo'] as $bit )
                $cells[] = html_writer::tag('li', $bit);
        }
        else
            $cells[] = html_writer::tag('li',get_string('guardian_view_default_current_work_non_assigned','local_zilink'));
        
        $cells[] = html_writer::tag('b',get_string('guardian_view_default_current_work_attempted','local_zilink'));
                                    
        if(!empty($work['attempted']))
        {
            foreach($work['attempted'] as $bit )
                $cells[] = html_writer::tag('li', $bit);
        }
        else
            $cells[] = html_writer::tag('li',get_string('guardian_view_default_current_work_non_attempted','local_zilink'));

        $cells[] = html_writer::tag('b',get_string('guardian_view_default_current_work_overdue','local_zilink'));
                
        if(!empty($work['overdue']))
        {
            foreach($work['overdue'] as $bit )
                $cells[] = html_writer::tag('li', $bit);
        }
        else
            $cells[] = html_writer::tag('li',get_string('guardian_view_default_current_work_non_overdue','local_zilink'));

        $table->data = array_chunk($cells, 1);
        $content = html_writer::table($table);
        return $content;
        
    } 

    function GetHomework($id)
    {
        global $CFG,$DB,$COURSE;
        
        require_once($CFG->dirroot.'/course/lib.php');
        
        if(file_exists($CFG->dirroot.'/blocks/progress/lib.php') || file_exists($CFG->dirroot.'/mod/zilinkhomework/lib.php'))
        {
            
            $content = '';
            $courses = enrol_get_users_courses($this->child->user->id);
            
            if(empty($courses))
                return '';  
            
            $student_course_list = array();
            foreach($courses as $course)
            {
                $category = $DB->get_record('course_categories',array('id' => $course->category));
                
                if(!isset($category->ctxpath))
                {
                    $tree = explode('/',$category->path);
                }
                else 
                {
                    $tree = explode('/',$category->ctxpath);
                }
                
                for($i = 1; $i < count($tree); $i++)
                {
                                
                    if($tree[$i] == $CFG->zilink_category_root)
                    {
                        $parent_category = $DB->get_record('course_categories',array('id' => $tree[$i+1]));
                        
                        if ($course->visible == 1 && ($id == 0 || $id == $parent_category->id)) {
                            $student_course_list[$parent_category->id][]  = $course->id;
                        }
                    }
                    elseif($CFG->zilink_category_root == 0 && $i == 1) 
                    {
                        $parent_category = $DB->get_record('course_categories',array('id' => $tree[$i]));
                        
                        
                        
                        if ($course->visible == 1 && ($id == 0 || $id == $parent_category->id)) {
                            $student_course_list[$parent_category->id][]  = $course->id;
                        }     
                    }
                }
            }
    
            $work = array();
    
             
            if(!empty($student_course_list))
            {
                
                
                $block = new stdClass;
    
                //$eventArray = array();
                
                $numevents = 0;
                $visibleEvents = 0;
                        
                //if(!empty($student_course_list))
                //{
                    
                    foreach ($student_course_list as $category => $courses)
                    {
                        foreach($courses as $course)
                        {
                            if(file_exists($CFG->dirroot.'/blocks/progress/lib.php')) 
                            {
                                require_once($CFG->dirroot.'/blocks/progress/lib.php');
                                
                                $plugin = new stdClass();
                                include($CFG->dirroot.'/blocks/progress/version.php');
                                
                                $legacy = true;
                                if($plugin->version == 2015091800 )
                                {
                                    $legacy = false;
                                }
                            
                                try 
                                {
                                    //$ctmp = $COURSE->id;
                                    //$COURSE->id = $course;        
                                
                                    $modules = block_progress_modules_in_use($course);
                
                                    $eventArray = array();
                                    
                                    $instances = $DB->get_records_sql("SELECT bi.id, bi.configdata 
                                                         FROM {block_instances} bi
                                                         JOIN {context} c ON bi.parentcontextid = c.id
                                                         WHERE instanceid = ?
                                                         AND bi.blockname = 'progress'", array($course));
                                    
                                    
                                    if (empty($instances)) {
                                            $instances = $DB->get_records('block_instances',array('blockname' => 'progress', 'parentcontextid' => 1, 'showinsubcontexts' =>1));
                                    }
                                    
                                    if (!empty($instances)) {
                                        
                                    
                                        $block->config = array();
                                        
                                        
                                        foreach($instances as $instance)
                                        {
                                            $now = time();
                                            $block->config = unserialize(base64_decode($instance->configdata));
                                            
                                            if(is_object($block->config)) 
                                            {
                                            
                                                $events = block_progress_event_information($block->config, $modules, $course, $this->child->user->id);
                                                
                                                if(is_array($events) && !empty($events)) 
                                                {
                                                    $attempts = block_progress_attempts($modules, $block->config, $events, $this->child->user->id, $instance->id);
                                                    
                                                    foreach ($events as $event) {
                                                        $attempted = $attempts[$event['type'].$event['id']];
                                                        
                                 
                                                        if ($attempted === true) 
                                                        {
                                                            if($event['type'] == 'quiz')
                                                            {
                                                                $work[$category]['attempted'][] = array( 'due' => date('d/m/Y',$event['expected']), 'name' => $event['name']);
                                                            }
                                                            else
                                                            {
                                                                $work[$category]['attempted'][] = array( 'due' => date('d/m/Y',$event['expected']), 'name' => '<a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cm']->id.'" target="_blank">'.$event['name'].'</a> ');
                                                            }
                                                        }
                                                        else if (((!isset($block->config->orderby) || $block->config->orderby == 'orderbytime') && $event['expected'] < $now) || ($attempted === 'failed')) 
                                                        {
                                                            if($event['type'] == 'quiz')
                                                            {
                                                                $work[$category]['overdue'][] = array( 'due' => date('d/m/Y',$event['expected']), 'name' => $event['name']);
                                                            }
                                                            else
                                                            {
                                                                $work[$category]['overdue'][] = array( 'due' => date('d/m/Y',$event['expected']), 'name' => '<a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cm']->id.'" target="_blank">'.$event['name'].'</a>');
                                                            }
                                                        }
                                                        else 
                                                        {
                                                            if($event['type'] == 'quiz')
                                                            {
                                                                $work[$category]['todo'][]= array( 'due' => date('d/m/Y',$event['expected']), 'name' =>  $event['name']);
                                                            }
                                                            else
                                                            {
                                                                $work[$category]['todo'][]= array( 'due' => date('d/m/Y',$event['expected']), 'name' =>'- <a href="'. $this->httpswwwroot.'/mod/'.$event['type'].'/view.php?id='.$event['cm']->id.'" target="_blank">'.$event['name'].'</a>');
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                            }
                                            //$COURSE->id = $ctmp; 
                                        }
                                    }
                                } 
                                catch (Exception $e){
                                    
                                }
                            }

                            if(file_exists($CFG->dirroot.'/mod/zilinkhomework/lib.php'))
                            {
            
                               require_once($CFG->dirroot.'/mod/zilinkhomework/lib.php');
                                
                               $mods = zilink_get_all_instances_in_course('zilinkhomework',$DB->get_record('course', array( 'id' => $course)), $this->child->user->id, true);
                           
                               foreach($mods as $mod)
                               {
                                   
                                   if($mod->visible == 1)
                                   {
                                       if($mod->completionpercent > 0) {
                                           $type = 'attempted';
                                       } else {
                                           $type = 'todo';
                                       }
                                       
                                       //if(groups_course_module_visible($mod,$this->child->user->id)) {
                                       
                                       if($mod->completionexpected > 0) {
                                           $work[$category][$type][] = array( 'due' => date('d/m/Y',$mod->completionexpected), 'name' => '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ');
                                       }
                                        else if($mod->availability == null) {
                                                $work[$category][$type][] = array( 'due' => 'Open Ended', 'name' => '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ');
                                           } else {
                                               
                                               $availibility = json_decode($mod->availability);
                                               $found = false;
                                               if(is_array($availibility->c))
                                               {
                                                   if(empty($availibility->c)) 
                                                   {
                                                        $work[$category][$type][] = array( 'due' => 'Open Ended', 'name' => '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ');
                                                   } else {
                                                  
                                                       foreach($availibility->c as $condition)
                                                       {
                                                           if($condition->type == 'date' && $condition->d == '<')
                                                           {
                                                               $found = true;
                                                               $work[$category][$type][] = array( 'due' => date('d/m/Y',$condition->t), 'name' => '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ');
                                                           } 
                                                       }
                                                   }
                                               } else {
                                                   $work[$category][$type][] = array( 'due' => 'Open Ended', 'name' => '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ');
                                               }
                                           }
                                       //}
                                   }
                               }
                           }
                            
                        }
                    }    
                //}   
            }           
    
            $categories = array();
            foreach ($work as $categoryid => $types)
            {
                $category = $DB->get_record('course_categories',array('id' => $categoryid));
                $categories[$categoryid] =  $category->name;
            }
            
            natsort($categories);
    
            $table              = new html_table();
            $table->cellpadding = '10px';    
            $table->width       = '68%';
            
            if ($CFG->zilink_guardian_view_default_homework_detail) {
                if($id == 0) {
                    $table->head        = array(get_string('guardian_view_default_subject','local_zilink'),get_string('guardian_view_default_homework_status','local_zilink'),get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                    $table->align       = array('left','left','left','right');
                } else {
                    $table->head        = array(get_string('guardian_view_default_homework_status','local_zilink'),get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                    $table->align       = array('left','left','right'); 
                }
            } else {
                if($id == 0) {
                    $table->head        = array(get_string('guardian_view_default_subject','local_zilink'),get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                    $table->align       = array('left','left','right');
                } else {
                    $table->head        = array(get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                    $table->align       = array('left','right'); 
                }
            }   
    
            $table->border      = '2px'; 
            $table->tablealign  = 'center';
            
            $cells = array();
            
            $count = 1;
            if(!empty($categories))
            {
                
                foreach($categories as $categoryid => $name)
                {
                    $count = 1;
                    $c = count($cells);
                    if(!empty($work[$categoryid]['todo']))
                    {
                        foreach ($work[$categoryid]['todo'] as $bit ) {
                            if ($id == 0) {
                                $cells[] = ($count == 1) ? html_writer::tag('b',$name) : '';
                            }
                            if ($CFG->zilink_guardian_view_default_homework_detail) {
                               $cells[] = get_string('guardian_view_default_current_work_assigned','local_zilink'); 
                            }
                            $cells[] = $bit['name'];
                            $cells[] = $bit['due'];
                            $count++;
                        }
                    }
                               
                    if(!empty($work[$categoryid]['attempted']))
                    {
                        foreach($work[$categoryid]['attempted'] as $bit ){
                            if ($id == 0) {
                                $cells[] = ($count == 1) ? html_writer::tag('b',$name) : '';
                            }
                            if ($CFG->zilink_guardian_view_default_homework_detail) {
                               $cells[] = get_string('guardian_view_default_current_work_attempted','local_zilink'); 
                            }
                            $cells[] = $bit['name'];
                            $cells[] = $bit['due'];
                            $count++;
                        }
                    }
                            
                    if(!empty($work[$categoryid]['overdue']))
                    {
                        foreach($work[$categoryid]['overdue'] as $bit ){
                            if ($id == 0) {
                                $cells[] = ($count == 1) ? html_writer::tag('b',$name) : '';
                            }
                            if ($CFG->zilink_guardian_view_default_homework_detail) {
                               $cells[] = get_string('guardian_view_default_current_work_overdue','local_zilink'); 
                            }
                            $cells[] = $bit['name'];
                            $cells[] = $bit['due'];
                            $count++;
                        }
                    }
                    
                    if($c == count($cells))
                    {
                        if ($id == 0) {
                            $cells[] = ($count == 1) ? html_writer::tag('b',$name) : '';
                        }
                        $cells[] = get_string('guardian_view_default_no_homework','local_zilink');
                        $cells[] = '';
                    }
    
                }
            } else {
                
               if ($id == 0) {
                        $cells[] = ($count == 1) ? html_writer::tag('b','') : '';
                }
               
                if ($CFG->zilink_guardian_view_default_homework_detail) {
                    $cells[] = '';
                }
                                
                $cells[] = get_string('guardian_view_default_no_homework','local_zilink');
                $cells[] = '';
            }
    
            if ($CFG->zilink_guardian_view_default_homework_detail) {
                $table->data = array_chunk($cells, ($id == 0) ? 4 : 3 );
            } else {
                $table->data = array_chunk($cells, ($id == 0) ? 3 : 2 );
            }
            $content = html_writer::table($table);
            return $content;
        }

/*
        if(file_exists($CFG->dirroot.'/mod/zilinkhomework/lib.php'))
        {
            
            require_once($CFG->dirroot.'/mod/zilinkhomework/lib.php');
            
            
            $courses = enrol_get_users_courses($this->child->user->id);
            
            if(empty($courses))
                return '';  
            
            $student_course_list = array();
            foreach($courses as $course)
            {
                $category = $DB->get_record('course_categories',array('id' => $course->category));
                
                if(!isset($category->ctxpath))
                {
                    $tree = explode('/',$category->path);
                }
                else 
                {
                    $tree = explode('/',$category->ctxpath);
                }
                
                for($i = 1; $i < count($tree); $i++)
                {
                                
                    if($tree[$i] == $CFG->zilink_category_root)
                    {
                        $parent_category = $DB->get_record('course_categories',array('id' => $tree[$i+1]));
                        
                        if ($course->visible == 1 && ($id == 0 || $id == $parent_category->id)) {
                            $student_course_list[$parent_category->id][]  = $course->id;
                        }
                    }
                    elseif($CFG->zilink_category_root == 0 && $i == 1) 
                    {
                        $parent_category = $DB->get_record('course_categories',array('id' => $tree[$i]));
                        
                        
                        
                        if ($course->visible == 1 && ($id == 0 || $id == $parent_category->id)) {
                            $student_course_list[$parent_category->id][]  = $course->id;
                        }     
                    }
                }
            }

            if(!empty($student_course_list))
            {
                
                $table              = new html_table();
                $table->cellpadding = '10px';    
                $table->width       = '68%';
                
                if ($CFG->zilink_guardian_view_default_homework_detail) {
                    if($id == 0) {
                        $table->head        = array(get_string('guardian_view_default_subject','local_zilink'),get_string('guardian_view_default_homework_status','local_zilink'),get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                        $table->align       = array('left','left','left','right');
                    } else {
                        $table->head        = array(get_string('guardian_view_default_homework_status','local_zilink'),get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                        $table->align       = array('left','left','right'); 
                    }
                } else {
                    if($id == 0) {
                        $table->head        = array(get_string('guardian_view_default_subject','local_zilink'),get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                        $table->align       = array('left','left','right');
                    } else {
                        $table->head        = array(get_string('description'),get_string('guardian_view_default_datedue','local_zilink'));
                        $table->align       = array('left','right'); 
                    }
                }
                
                $table->border      = '2px'; 
                $table->tablealign  = 'center';
                
                $cells = array(); 
                
                foreach ($student_course_list as $category => $courses)
                {
                    $cat = $DB->get_record('course_categories',array('id' => $category));
                    
                    $count = 1;
                    
                    foreach($courses as $course)
                    {
                           
                           $mods = zilink_get_all_instances_in_course('zilinkhomework',$DB->get_record('course', array( 'id' => $course)), $this->child->user->id, true);
                           
                           foreach($mods as $mod)
                           {
                               var_dump($mod);
                               if($mod->visible == 1)
                               {
                                   if ($id == 0) {
                                        $cells[] = ($count == 1) ? html_writer::tag('b',$cat->name) : '';
                                   }
                                   
                                   if ($CFG->zilink_guardian_view_default_homework_detail) {
                                       $cells[] = get_string('guardian_view_default_current_work_assigned','local_zilink'); 
                                    }
                                   
                                   $cells[] = '<a href="'. $this->httpswwwroot.'/mod/zilinkhomework/view.php?id='.$mod->coursemodule.'" target="_blank">'.$mod->name.'</a> ';
                                   
                                   if($mod->availability == null) {
                                        $cells[] = 'Open Ended';
                                   } else {
                                       
                                       $availibility = json_decode($mod->availability);
                                       
                                       if(is_array($availibility->c))
                                       {
                                           foreach($availibility->c as $condition)
                                           {
                                               if($condition->type == 'date' && $condition->d == '<')
                                               {
                                                   $cells[] = date('d/m/Y',$condition->t);
                                               }
                                           }
                                       } else {
                                           $cells[] = 'Open Ended';
                                       }
                                   }
                                
                                   $count++; 
                               }
                           }
                           
                    }
                }
                
                if ($CFG->zilink_guardian_view_default_homework_detail) {
                    $table->data = array_chunk($cells, ($id == 0) ? 4 : 3 );
                } else {
                    $table->data = array_chunk($cells, ($id == 0) ? 3 : 2 );
                }
                $content = html_writer::table($table);
                return $content;
                  
            }
        }*/
        return '';
        
    } 
    
}
