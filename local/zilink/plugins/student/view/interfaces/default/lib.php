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

define('ZILINK_ASSESSMENT',-1);
define('ZILINK_ATTENDANCE',-2);


require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/data.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/person.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/base.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/core/panel.php');
require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/timetable/lib.php');
 
class ZiLinkStudentView extends ZiLinkBase {
    
    function __construct($courseid){
        global $CFG,$DB;
        
        $this->data = new ZiLinkData();
        $this->person = new ZiLinkPerson();
        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        
        $this->child = $this->person->GetPersonalData(array('details','extended_details','attendance','assessment'));
                
        parent::__construct($courseid);
        include(dirname(dirname(dirname(dirname(__FILE__)))).'/admin/defaults.php');
    }

    function View() {
        
        $content = $this->GetStudentDetailPanels();
        $content .= $this->GetAssessmentOverviewGraph();
        $content .= $this->GetAttendanceOverviewGraph();
        $content .= $this->GetCurrentWorkOverview();
        return $content;
    }
    
    private function GetAssessmentOverviewGraph()
    {
        global $CFG,$DB,$PAGE;
        
        $data = array();

        $gradesets = $this->data->GetGlobalData('assessment_gradesets');
         
        $count = 1;

        $this->child = $this->person->GetPersonalData('assessment');

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
                
                $gradetypestrings[$resulttype] =  $assessment->Attribute('resulttype');
                
                $data[$assessment->Attribute('session')][strtolower(str_replace(' ','',$assessment->Attribute('subject')))]['category'] = $assessment->Attribute('subject');
                $data[$assessment->Attribute('session')][strtolower(str_replace(' ','',$assessment->Attribute('subject')))][$resulttype] = (int)$grade->Attribute('value');

                if(!isset($max[strval($assessment->Attribute('session'))]) || $max[strval($assessment->Attribute('session'))] < (int)$grade->Attribute('value')) {
                    $max[strval($assessment->Attribute('session'))] = (int)$grade->Attribute('value');
                }
                
                if(!isset($min[strval($assessment->Attribute('session'))]) ||$min[strval($assessment->Attribute('session'))] > (int)$grade->Attribute('value') || $min == 0) {
                    $min[strval($assessment->Attribute('session'))] = (int)$grade->Attribute('value'); 
                }
             
            }
            $count++;
        }

        

        if(empty($data))
        {
            return '';
        }
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_student_view_assessment_overview_chart',
                        'fullpath'  =>  '/local/zilink/plugins/student/view/interfaces/default/module.js',
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
                                         
        $PAGE->requires->js_init_call('M.local_zilink_student_view_assessment_overview_chart.init', $jsdata, false, $jsmodule);
        
        $cells = array_merge(array('<div id="zilink_student_view_assessment_overview_chart" class="zilink_chart"></div>'),array($this->GetAssessmentOverviewComments($attainment,$targets)));

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
            if(isset($targets[$index])) {
                if($attainment < $targets[$index])
                    $monitor['below']++;
                elseif($attainment == $targets[$index])
                    $monitor['level']++; 
                elseif($attainment > $targets[$index])
                    $monitor['above']++;    
            }
        }
        
        $content = $CFG->zilink_student_view_default_assessment_overview_general_comment;
        if(isset($CFG->zilink_student_view_default_assessment_overview_below_trigger) && isset($CFG->zilink_student_view_default_assessment_overview_below_comment))
        {
            if(($monitor['below'] >= $CFG->zilink_student_view_default_assessment_overview_below_trigger) && ($CFG->zilink_student_view_default_assessment_overview_below_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_student_view_default_assessment_overview_below_comment;
        }
        if(isset($CFG->zilink_student_view_default_assessment_overview_level_trigger) && isset($CFG->zilink_student_view_default_assessment_overview_level_comment))
        {   
            if(($monitor['level'] >= $CFG->zilink_student_view_default_assessment_overview_level_trigger) && ($CFG->zilink_student_view_default_assessment_overview_level_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_student_view_default_assessment_overview_level_comment;
        }
        if(isset($CFG->zilink_student_view_default_assessment_overview_above_trigger) && isset($CFG->zilink_student_view_default_assessment_overview_above_comment))
        {   
            if(($monitor['above'] >= $CFG->zilink_student_view_default_assessment_overview_above_trigger) && ($CFG->zilink_student_view_default_assessment_overview_above_trigger <> -1))
                $content .= '<br><br>'.$CFG->zilink_student_view_default_assessment_overview_above_comment;
        }
        
        $content = str_replace('[[FIRSTNAME]]', $this->child->user->firstname, $content);
        return $content;
    }
    
    private function GetAttendanceOverviewGraph()
    {
        
        global $CFG,$PAGE;
        
        $this->child = $this->person->GetPersonalData('attendance');
        
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
                        if($code->Attribute('key') == 'I')
                            $tmp[3]['authorisedabsence'] = (!isset($tmp[3]['authorisedabsence'])) ? (int)$code->Attribute('value') : $tmp[3]['authorisedabsence'] + $code->Attribute('value');
                        else
                            $tmp[4]['unauthorisedabsence'] = (!isset($tmp[4]['unauthorisedabsence'])) ? (int)$code->Attribute('value') : $tmp[4]['unauthorisedabsence'] +$code->Attribute('value');
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
    
        $cells = array_merge(array('<div class="rotate">Weeks</div><div id="zilink_student_view_attendance_overview_chart" class="zilink_chart">'),array($this->GetAttendanceGraphComments($tmp)));

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
                        'name'  =>  'local_zilink_student_view_attendance_overview_chart',
                        'fullpath'  =>  '/local/zilink/plugins/student/view/interfaces/default/module.js',
                        'requires'  =>  array('base', 'node', 'io','charts','json'),
                        'strings' => array (    array('present', 'local_zilink'),
                                            array('authorisedabsence', 'local_zilink'),
                                            array('unauthorisedabsence', 'local_zilink'),
                                            array('late', 'local_zilink')
                                    )); 

        $jsdata = array($data,$max,sesskey());

        $PAGE->requires->js_module($jsmodule);
        $PAGE->requires->js_init_call('M.local_zilink_student_view_attendance_overview_chart.init', $jsdata, false, $jsmodule);
        
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
               
                if(isset($CFG->{'zilink_student_view_default_attendance_overview_'.$index.'_below_trigger'}))
                {
                    if($value < (int)$CFG->{'zilink_student_view_default_attendance_overview_'.$index.'_below_trigger'})
                        $monitor[$index]['below']++;
                }
                
                if(isset($CFG->{'zilink_student_view_default_attendance_overview_'.$index.'_above_trigger'}))
                {   
                    if($value > (int)$CFG->{'zilink_student_view_default_attendance_overview_'.$index.'_above_trigger'})
                        $monitor[$index]['above']++;
                }
            }
        }
        
        $content = $CFG->zilink_student_view_default_attendance_overview_general_comment;
        
        foreach($monitor as $type => $item)
        {
            
            foreach($item as $index => $value)
            {
                if($index == 'below' && $value > 0)
                {
                    
                    if(
                     isset($CFG->{'zilink_student_view_default_attendance_overview_'.$type.'_'.$index.'_comment'}))
                    {
                            $content .= '<br><br>'.$CFG->{'zilink_student_view_default_attendance_overview_'.$type.'_'.$index.'_comment'};
                    }
                }
                if($index == 'above' && $value > 0)
                {
                    if(isset($CFG->{'zilink_student_view_default_attendance_overview_'.$type.'_'.$index.'_comment'}))
                    {
                            $content .= '<br><br>'.$CFG->{'zilink_student_view_default_attendance_overview_'.$type.'_'.$index.'_comment'};
                    }
                }
            }
        }
        
        $content = str_replace('[[FIRSTNAME]]', $this->child->user->firstname, $content);
        return $content;
    }
    
    
    function GetStudentDetailPanels()
    {
        
        
        

        $panel = new ZiLinkPanel();
        $panel->SetTitle(get_string('student_view_default_student_details','local_zilink','dave'));
        
        $cells = array();
        
        if(isset($this->child->extended_details) && $this->child->extended_details <> null)
        {
            
        
        $cells[] = array(get_string('student_view_default_student_name','local_zilink'),$this->child->extended_details->PersonalInformation->Name->GivenName .' '.$this->child->extended_details->PersonalInformation->Name->FamilyName);
        $cells[] = array(get_string('student_view_default_student_dob','local_zilink'),date('d/m/Y',strtotime($this->child->extended_details->PersonalInformation->Demographics->BirthDate)));
        $cells[] = array(get_string('student_view_default_student_gender','local_zilink'),$this->child->extended_details->PersonalInformation->Demographics->Gender);
        $cells[] = array(get_string('student_view_default_student_house','local_zilink'),($this->child->details->person->schoolregistration->attribute('house') == 'UNKNOWN') ? '-' : $this->child->details->person->schoolregistration->attribute('house'));
        $cells[] = array(get_string('student_view_default_student_year_group','local_zilink'),($this->child->details->person->schoolregistration->attribute('year')  == 'UNKNOWN') ? '-' : $this->child->details->person->schoolregistration->attribute('year'));
        $cells[] = array(get_string('student_view_default_student_registration_group','local_zilink'),($this->child->details->person->schoolregistration->attribute('registration')  == 'UNKNOWN') ? '-' : $this->child->details->person->schoolregistration->attribute('registration'));
        }
        $panel->SetWidth('40%');
        $panel->SetCSS('boxaligncenter zilink_student_view_table_small_center');
        $panel->SetContent($cells);
            
        return $panel->Display();

    }

    private function GetTodaysTimetable()
    {
        
        $panel = new ZiLinkPanel();
        $timetable = new ZiLinkTimetable(1);

        if(date('N') > 5)
        {
            $panel->SetTitle(get_string('student_view_default_mondays_timetable','local_zilink'));
        }
        else
        {
            $panel->SetTitle(get_string('student_view_default_todays_timetable','local_zilink'));
                
        } 
 
        $panel->SetContent(array_chunk(array($timetable->GetTodaysTimetable(array('user_idnumber' => $this->child->user->idnumber))),1));
        $panel->SetWidth('40%');
        $panel->SetCSS('right zilink_student_view_table_small_right');
        
        return $panel->Display();

    }

    private function GetStudentAttendanceSummary()
    {
        
        if($this->child->attendance == null);
            return '';
            
        global $OUTPUT;
        
        $content = '';
        $panel = new ZiLinkPanel();
        $panel->SetTitle(get_string('student_view_default_recent_attendance','local_zilink'));
        $panel->SetWidth('68%');
        $panel->SetCSS('generaltable boxaligncenter zilink_table_width');

        $row = array();
        
        if(isset($this->child->attendance->attendance->snapshot))
        {
            $firstweek = date('d/m/Y',strtotime('-1 Week',strtotime('previous monday',strtotime($this->child->attendance->attendance->snapshot->Attribute('end')))));
            $secondweek = date('d/m/Y',strtotime('previous monday',strtotime($this->child->attendance->attendance->snapshot->Attribute('end'))));
        }
        else
        {
            $firstweek = date('d/m/Y',strtotime("-1 Week previous Monday",$this->geteffectivedate()));
            $secondweek = date('d/m/Y',strtotime("previous Monday",$this->geteffectivedate()));
        }
        
        $days = array(date('d/m/Y',strtotime("-1 Week last Monday",$this->geteffectivedate())),'Monday','Tuesday','Wednesday','Thursday','Friday');   
        $marks = $this->GetStudentAttendanceMarkImages();
    
        $cells = array_merge(array('AM'),$marks[1]['am'],array('AM'),$marks[2]['am'],array('PM'),$marks[1]['pm'],array('PM'),$marks[2]['pm']);

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '95%';
        $table->head        = array('Week Begining - '.$firstweek,'Monday','Tuesday','Wednesday','Thursday','Friday','Week Begining - '.$secondweek,'Monday','Tuesday','Wednesday','Thursday','Friday');
        $table->align       = array('center', 'center', 'center', 'center','center','center','center', 'center', 'center', 'center','center','center');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        $table->attributes['student'] = 'generaltable zilink_student_view_table';
        
        $table->data = array_chunk($cells, 12);
        $content .= html_writer::table($table);         
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '40%';
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
                                $OUTPUT->pix_icon('student/view/interfaces/default/button-green', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')),
                                $OUTPUT->pix_icon('student/view/interfaces/default/button-purple', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('student/view/interfaces/default/button-blue', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('student/view/interfaces/default/button-red', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('student/view/interfaces/default/dialog-declare', '', 'local_zilink',array('height' => '45px' , 'width' => '45px')), 
                                $OUTPUT->pix_icon('student/view/interfaces/default/appointment-soon', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'))); 
                                        
                                
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
        foreach($marks as $week => $sessions)
        {
            foreach($sessions as $session => $marks)
            {
                if(!isset($count[$session]))
                    $count[$session] = 0;
                    
                foreach($marks as $index => $mark)
                {
                    if(((10 - $count[$session]) <= $CFG->zilink_student_view_default_attendance_overview_delay) && !$this->isCurrentWeekHoliday())
                        $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/appointment-soon', '', 'local_zilink');
                    else
                    {
                        switch($mark)
                        {
                            case "#":
                            case "Y":
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/dialog-declare', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            case "/" :
                            case "\\":
                            case "B":
                            case "D":
                            case "B":
                            case "V":
                            case "W":
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/button-green', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            case "C":
                            case "E":
                            case "F":
                            case "H":
                            case "I":
                            case "J":
                            case "M":
                            case "P": 
                            case "R": 
                            case "S": 
                            case "T":                     
                                  $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/button-blue', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                  break;
                            case "G":
                            case "N":
                            case "O":
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/button-red', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            case "L":
                            case "U":
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/button-purple', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
                                break;
                            default:
                                $icons[$week][$session][$index] = $OUTPUT->pix_icon('student/view/interfaces/default/appointment-soon', '', 'local_zilink',array('height' => '45px' , 'width' => '45px'));
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
        
        $marks = $this->child->attendance->attendance->snapshot->Attribute('marks');
        $length = strlen($marks);
        $adjusted = '';
        $flag = false;
        
        if(date('w') == 0)
            $count = 2;
        else 
            $count = 0;
            
        for($i = 0; $i < $length; $i++)
        {
            $chr = substr($marks, $i,1);
            
            if(($flag == true) && (($count >=4 && ord($chr) == 35)||(ord($chr) <> 35))) {
                $adjusted .= $chr;
            }
            
            if(ord($chr) == 35)
            {
                $flag = true;
                $count++;
            }   
        }
        
        $start = $this->child->attendance->attendance->snapshot->Attribute('start');
        $end =  $this->child->attendance->attendance->snapshot->Attribute('end');
        $days = (strtotime('this friday') - $this->geteffectivedate()) / (60 * 60 * 24);
        $days += (strtotime($today) - strtotime($end)) / (60 * 60 * 24);
        
        if($days > 0)
        {
            if($this->IsCurrentWeekHoliday())
                $adjusted .= str_repeat('#',$days*2);
            else
                $adjusted .= str_repeat('-',$days*2);
        }
    
        return $adjusted;
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
                                                            {grade_grades}.timecreated as timestamp
                                                    FROM    {grade_grades} 
                                                    JOIN    {grade_items} 
                                                    WHERE   {grade_grades}.itemid = {grade_items}.id 
                                                    AND     {grade_grades}.rawgrade IS NOT NULL
                                                    AND     courseid IN (".implode(',',$student_course_list) .")
                                                    AND     userid = ".$this->child->user->id ."
                                                    ORDER BY type, timestamp");
            }
    
            if(empty($grades_data))
                return '';
                
            foreach ($grades_data as $grade_data)
            {
                $grades[$grade_data->type][] = array(   'grade' => (int)$grade_data->grade/(int)$grade_data->max  * 100 . '% - '. $grade_data->name .' - '.date('d/m/Y',$grade_data->timestamp),
                                        'feedback' => $grade_data->feedback);
            }
                            
            $table              = new html_table();
            $table->cellpadding = '10px';    
            $table->width       = '68%';
            $table->head        = array(get_string('student_view_default_submitted_work','local_zilink',$parent_category->name));
            $table->align       = array('left');
            $table->border      = '2px'; 
            $table->tablealign  = 'center';
            
            $cells = array();
            $cells[] = get_string('student_view_default_submitted_work_desc','local_zilink',$this->child->user);
            
            $string = '';
            foreach($grades as $key => $items)
            {
                
                $string = '<div id="titlebar"><div student="title">'.get_string($key,'block_progress').'</div></div><div><ul>';
                    
                foreach ($items as $grade)
                {
                    $string .= '<li>'.$grade['grade'].'<ul><li>'.$grade['feedback'].'</li></ul></li>';
                }
                
                $string .= '</ul></div>';
                $cells[] = $string;
            }
    
            $table->data = array_chunk($cells, 1);
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
            
            if (isset($mdl_category->ctxpath)) {
                $categories = explode('/',$category->ctxpath);
            } else {
                $categories = explode('/',$category->path);
            }
            
            foreach($categories as $category)
            {
                if($category == $id)
                {
                    $enrolinstances = enrol_get_instances($course->id,true);
                    $enrolments = $DB->get_records('enrol',array('courseid' => $course->id, 'roleid' => 5, 'enrol'=> 'zilink_cohort' ));
                    foreach ($enrolments as $enrolment)
                    {
                        if($DB->record_exists('cohort_members',array('userid' => $this->child->user->id,'cohortid' =>$enrolment->customint1)))
                        {
                            if(!in_array($enrolment->customint1,$cohorts))
                                $cohorts[] = $enrolment->customint1;
                        }
                    }
                }
            }
        }
        
        foreach($cohorts as $cohort)
        {
            $teachers = $DB->get_records('zilink_cohort_teachers',array('cohortid' => $cohort));
            foreach($teachers as $teacher)
            {
                $user = $DB->get_record('user',array('id' => $teacher->userid));
                $cells[] = fullname($user);
                
                if($this->person->Security()->IsAllowed('local/zilink:student_view_subjects_teacher_details_email'))
                {
                    $cells[] = $user->email;
                }
                else 
                    {
                        $cells[] ='';
                    }
            }
        }
        
        if(empty($cells))
            return '';
        
        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '48%';
        if($this->person->Security()->IsAllowed('local/zilink:student_view_subjects_teacher_details_email'))
        {
            $table->head        = array('Teacher','Email Address');
        }
        else {
           $table->head        = array('Teacher','Email Address');
        }
        $table->align       = array('left', 'left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $table->data = array_chunk($cells, 2);
        return html_writer::table($table);  
    }

    function GetCurrentWorkOverview()
    {
        global $CFG,$DB,$COURSE;
        $id = 0;
        require_once($CFG->dirroot.'/course/lib.php');
        
        if(file_exists($CFG->dirroot.'/blocks/progress/lib.php') || file_exists($CFG->dirroot.'/mod/zilinkhomework/lib.php'))
        {
            
            $content = '';

            if(!isset($this->child->user->id)) 
                return '';
            
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
                        if(file_exists($CFG->dirroot.'/blocks/progress/lib.php')) {
                            require_once($CFG->dirroot.'/blocks/progress/lib.php');
                        
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
                                    
                                        $events = block_progress_event_information($block->config, $modules, $course, $this->child->user->id);
    
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
                                    //$COURSE->id = $ctmp; 
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
                                   
                                   if(groups_course_module_visible($mod,$this->child->user->id)) {
                                   
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
                                   }
                               }
                           }
                       }
                    }
                }    
            }
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
        $table->head        = array(get_string('student_view_default_current_work','local_zilink','All'));
        $table->align       = array('left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $cells = array();
        /*
        $cells[] = get_string('student_view_default_current_work_desc','local_zilink',$this->child->user);
        
        
        if(!empty($categories))
        {
            foreach($categories as $categoryid => $name)
            {
                $cells[] = html_writer::tag('b',$name);
                
                $cells[] = html_writer::tag('b',get_string('student_view_default_current_work_assigned','local_zilink'));
                
                if(!empty($work[$categoryid]['todo']))
                {
                    foreach($work[$categoryid]['todo'] as $bit )
                        $cells[] = html_writer::tag('li', $bit);
                }
                else
                    $cells[] = html_writer::tag('li',get_string('student_view_default_current_work_non_assigned','local_zilink'));
                
                $cells[] = html_writer::tag('b',get_string('student_view_default_current_work_attempted','local_zilink'));
                                            
                if(!empty($work[$categoryid]['attempted']))
                {
                    foreach($work[$categoryid]['attempted'] as $bit )
                        $cells[] = html_writer::tag('li', $bit);
                }
                else
                    $cells[] = html_writer::tag('li',get_string('student_view_default_current_work_non_attempted','local_zilink'));
        
                $cells[] = html_writer::tag('b',get_string('student_view_default_current_work_overdue','local_zilink'));
                        
                if(!empty($work[$categoryid]['overdue']))
                {
                    foreach($work[$categoryid]['overdue'] as $bit )
                        $cells[] = html_writer::tag('li', $bit);
                }
                else
                {
                    $cells[] = html_writer::tag('li',get_string('student_view_default_current_work_non_overdue','local_zilink'));
                }
            }
        }
        $table->data = array_chunk($cells, 1);
        $content = html_writer::table($table);
        return $content;    
         * */
         
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
                        $cells[] = ($count == 1) ? html_writer::tag('b',$name) : '';
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
        $table->head        = array(get_string('student_view_default_current_work','local_zilink',$parent_category->name));
        $table->align       = array('left');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        
        $cells = array();
        $cells[] = get_string('student_view_default_current_work_desc','local_zilink',$this->child->user);
        $cells[] = html_writer::tag('b',get_string('student_view_default_current_work_assigned','local_zilink'));
        
        if(!empty($work['todo']))
        {
            foreach($work['todo'] as $bit )
                $cells[] = html_writer::tag('li', $bit);
        }
        else
            $cells[] = html_writer::tag('li',get_string('student_view_default_current_work_non_assigned','local_zilink'));
        
        $cells[] = html_writer::tag('b',get_string('student_view_default_current_work_attempted','local_zilink'));
                                    
        if(!empty($work['attempted']))
        {
            foreach($work['attempted'] as $bit )
                $cells[] = html_writer::tag('li', $bit);
        }
        else
            $cells[] = html_writer::tag('li',get_string('student_view_default_current_work_non_attempted','local_zilink'));

        $cells[] = html_writer::tag('b',get_string('student_view_default_current_work_overdue','local_zilink'));
                
        if(!empty($work['overdue']))
        {
            foreach($work['overdue'] as $bit )
                $cells[] = html_writer::tag('li', $bit);
        }
        else
            $cells[] = html_writer::tag('li',get_string('student_view_default_current_work_non_overdue','local_zilink'));

        $table->data = array_chunk($cells, 1);
        $content = html_writer::table($table);
        return $content;
        
    } 
}