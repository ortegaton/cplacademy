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

require_once(dirname(dirname(__FILE__)).'/core/data.php');
require_once(dirname(dirname(__FILE__)).'/core/person.php');
require_once(dirname(dirname(__FILE__)).'/core/base.php');
 
class ZiLinkTimetable extends ZiLinkBase {
    
    function __construct($courseid = null){
        global $CFG,$DB;
        
        $this->course = new stdClass();
        $this->course->id = $courseid;
        
        $this->timetable_count = 0;
        $this->timetable_number_weeks = 0;
        $this->timetable_number_days = 0;
        
        $this->academic_year = array('start' => 0, 'end' => 0);  
        $this->data = new ZiLinkData();

        $this->LoadTimetableSettings();

        $this->SetupAcademicYear();
        $this->SetupAcademicYearWeeks();
        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $this->person = new ZiLinkPerson();
    }
    /*
    private function LoadTimetableSettings()
    {   
             var_dump('here');
            $this->timetable_number_weeks = $this->data->GetGlobalData('timetable',true)->Attribute('weeks');
            $this->timetable_number_days =  $this->data->GetGlobalData('timetable',true)->Attribute('days');
            
        }  catch (Exception $e) {
            
            throw new Exception("Global Timetable Data Missing", 1);
            
        }

    }
    */
    private function LoadJavaScript($args)
    {
        global $PAGE;
        
        $jsdata = array($this->httpswwwroot,$args['user_idnumber'],sesskey());
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_timetable',
                        'fullpath'  =>  '/local/zilink/plugins/timetable/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

        $PAGE->requires->js_init_call('M.local_zilink_timetable.init', $jsdata, false, $jsmodule);
        
    }
    
    public function GetTimetable($args)
    {
        global $CFG,$DB,$USER,$PAGE;
  
        $args = $this->DefaultArguments(array(   'display_full_timetable' => true,
                                                            'page_layout' => 'base',
                                                            'ajax_call' => false, 
                                                            'user_id' => optional_param('id', 0, PARAM_INT),
                                                            'requested_by' =>'timetable',
                                                            'weekoffset' => 0,
                                                            'user_idnumber' => $USER->idnumber
                                                         ),$args);
        
        $weeks = $this->timetable_number_weeks;
        $days =  $this->timetable_number_days;
        
        $dayname = $this->GetDayNames();
        $currrentweek = $this->GetWeek();
        
        $firstweekstart = 1;
        $secondweekstart = $days +1;
        $weekoffset = $args['weekoffset'];
        $content = '';
        
        if (!$args['ajax_call']) $this->LoadJavaScript($args);
        if (!$args['ajax_call']) $content = $this->GetLegend();
        
        
        if($args['requested_by'] == 'timetable')
        {
            if($args['page_layout'] <> 'mydashboard' )
            {
                $content .= '<div style="float:right; display: inline; width:auto;">';                                             
                $content .=    '<div style="float:right;  width:10em"><input style="width:10em" type="button" value="'.get_string('timetable_back','local_zilink').'" onClick="window.location.href=\''.$this->httpswwwroot.'/course/view.php?id='.$this->course->id.'\'"></div>';
            }
            
            if($args['user_id'] <> $USER->id)
            {
                if($this->person->Security()->IsAllowed('block/zilink:bookings_rooms_viewown')) {
                        $content .= '<div style="float:right;  width:10em"><input style="width:10em" type="button" value="Room Bookings" onClick="window.location.href=\''.$this->httpswwwroot.'/blocks/zilink/plugins/timetable/bookings.php?cid='.$this->course->id.'&instanceid='.$this->instance->id.'&sesskey='.sesskey().'\'"> </div>';
                }
                elseif($this->person->Security()->IsAllowed('block/zilink:roombooking_viewalternative')) {

                    $content .= '<div style="float:right;  width:10em"><input style="width:10em" type="button" value="Room Bookings" onClick="window.open(\''.$CFG->zilink_roombooking_alternative_link.'\',\'Room Booking\',\'width=400,height=200,toolbar=yes,location=yes,directories=yes,status=yes,menubar=yes,scrollbars=yes,copyhistory=yes,resizable=yes\')"> </div>';
                }
                
                if($this->person->Security()->IsAllowed('block/zilink:roombooking_manageroommaintenance')){
                    $content .= '<div style="float:right;  width:10em"><input style="width:10em" type="button" value="'.get_string('roombooking_room_maintenance','local_zilink').'" onClick="window.location.href=\''.$this->httpswwwroot.'/blocks/zilink/plugins/timetable/maintenance.php?cid='.$this->course->id.'&instanceid='.$this->instance->id.'&sesskey='.sesskey().'\'"> </div>';
                } 
                
            }
            $content .= '</div>';
        }

        $content .= '<div class="clearer"></div>';
        $content .= '</div>';
        $content .= '<div class="clearer"></div>';
        $content .= '<div class="clearer"></div>';
        $content .= '<div id="panel">';
        $content .= '<div class="timetable">';
        
        $count = 0;

        if(!isset($CFG->zilink_timetable_advance_booking))
            $CFG->zilink_timetable_advance_booking = 1;
        
        $currentperiodtimes = array();
        $previousperiodtimes = array();
        
        $firstweekbeginning = null;
        $secondweekbeginning = null;
        $secondweekoffset = 0;
        $gotsecondweek = false;
        
        $data = $this->person->GetPersonData('timetable',$args['user_idnumber'],true);

        /*
        foreach ($tt->timetable as $ttnode)
        {
            if($count <> $CFG->zilink_timetable_offset)
            {   
                $count++;
                continue;
            }
        */    
            foreach ($data->timetable->day as $day)
            {
                if($day->attribute('index') < $firstweekstart) 
                    continue;
                
                if($args['display_full_timetable'] == false && $this->timetable_number_weeks == 2)
                {
                    if($currrentweek == 1 )
                    {
                        if($day->attribute('index') >= $secondweekstart)
                            continue;
                    }
                    elseif($currrentweek == 2 ) 
                    {
                        if($day->attribute('index') < $secondweekstart)
                            continue;
                    }
                }
   
                if($day->attribute('index') == $firstweekstart || $day->attribute('index') == $secondweekstart )
                {

                    if($day->attribute('index') == $secondweekstart)
                    {
                        $content .= '<div style="display: table-row; width:auto; height:10px"></div>';
                    }
                    
                    $content .= '<div style="display: table-row; width:auto;">';
          
                    $content .= '<div class="timetable-days">';
                    if(($CFG->zilink_timetable_week_format == 0) || $this->timetable_number_weeks == 1)
                    {
                        $content .= ' <div id="week" style="width:100%"></div>';
                    }
                    else
                    {
               
                        if($CFG->zilink_timetable_week_format == 2)
                        {
                            if($day->attribute('index') == $firstweekstart)
                                $content .= 'Week 1';
                            elseif($day->attribute('index') == $secondweekstart)
                                $content .= 'Week 2';
                        }
                        elseif($CFG->zilink_timetable_week_format == 1)
                        {
                            if($day->attribute('index') == $firstweekstart)
                                $content .= 'Week A';
                            elseif($day->attribute('index') == $secondweekstart)
                                $content .= 'Week B';
                        }
                    }
                    if($args['requested_by'] == 'timetable'  && $args['page_layout'] <> 'maydashboard' )
                    {
                       if($day->attribute('index') == $firstweekstart) {
                            
                                $content .= ' <div id="weekselector" >';
                                $content .= '   <select id="zilinkweekselector" style="width:90%">';
                                    
                                $selected = '';
                                $holidayoffset = 0;
                                
                                for ($i = 0; $i <= $CFG->zilink_bookings_rooms_weeks_in_advance; $i++)
                                {
                                    if($args['weekoffset'] == $i)
                                        $selected = ' selected ';
                                    
                                    if($weeks == 1 && $i == 0) {
                                        $monday = strtotime('monday this week',$this->geteffectivedate());
                                     
                                    } else if ($weeks == 2 && $this->GetWeek() == 1 && $i == 0) {
                                        $monday = strtotime('monday this week',$this->geteffectivedate());
                                    } else if ($weeks == 2 && $this->GetWeek() == 2 && $i == 0) {
                                        $monday = strtotime('monday last week',$this->geteffectivedate());
                                    }
                                    
                                    if($monday >= $this->academic_year['end']) {
                                        continue;
                                    }
                                    /*
                                    if($this->IsCurrentWeekHoliday($monday)) {
                                        $monday = strtotime('monday last week',$monday);
                                    }
                                    */
                                    
                                    if($i == 0 ) {
                                        
                                        if(!empty($selected)) {
                                            $firstweekbeginning = $monday;
                                        }
                                        
                                        $content .= '<option value="0"'.$selected.'>WB - '.date('d/m/Y',$monday).'</option>';
                                        $originalmonday = $monday;
                                        
                                    } else {
                                        
                                        /*
                                        if($this->IsCurrentWeekHoliday(strtotime('+1 week',$monday))) {
                                            $monday = strtotime('+1 week',$monday);
                                            $holidayoffset++;
                                        }
                                        if($this->IsCurrentWeekHoliday(strtotime('+1 week',$monday))) {
                                            $monday = strtotime('+1 week',$monday);
                                            $holidayoffset++;
                                        }
                                        */
                                        
                                        if((!empty($firstweekbeginning) && !$gotsecondweek) || (empty($firstweekbeginning) && !empty($selected) && !$gotsecondweek))  {
                                            
                                            if(empty($firstweekbeginning) && (!empty($selected))) {
                                                $firstweekbeginning = strtotime('+'.$weekoffset. ' weeks',$originalmonday);
                                            }
                                            
                                            $secondweekbeginning = strtotime('+1 weeks',$firstweekbeginning);
                                            //$secondweekoffset = $holidayoffset;
                                            $gotsecondweek = true;
                                        } 
                                                                            
                                        $content .= '<option value="'.(($i*$weeks)).'"'.$selected.'>WB - '.date('d/m/Y',strtotime('+'.$weeks. ' week',$monday)).'</option>'; 
                                        $monday = strtotime('+'.$weeks. ' week',$monday);
                                    }
                                        
                                    $selected = ''; 
                                }
                                $content .= '   </select>';
                                $progressparams = array('id' => 'zilink_timetableupdateprogress', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/loading_small', 'moodle'),'alt' => get_string('timetable_loading', 'local_zilink'));
                                $content .= html_writer::empty_tag('img', $progressparams);
                                $progressparams = array('id' => 'zilink_timetableupdatefailed', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/cross_red_big', 'moodle'),'alt' => get_string('timetable_updatefailed', 'local_zilink'));
                                $content .= html_writer::empty_tag('img', $progressparams);
                                $progressparams = array('id' => 'zilink_timetableupdatesuccess', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/tick_green_big', 'moodle'),'alt' => get_string('timetable_updatesuccess', 'local_zilink'));
                                $content .= html_writer::empty_tag('img', $progressparams);
                                $content .= ' </div>';
                        } else if($day->attribute('index') == $secondweekstart) {
                           $content .= '<br>WB - '.date('d/m/Y',$secondweekbeginning);
                           //$secondweekoffset = $weekoffset + floor(($secondweekbeginning - $firstweekbeginning) /(60*60*24*7));
                        }
        
                    } else {
                        if($weeks == 1) {
                            $firstweekbeginning = strtotime('monday this week',$this->geteffectivedate());
                            
                        } else if ($weeks == 2 && $this->GetWeek() == 1) {
                            $firstweekbeginning = strtotime('monday this week',$this->geteffectivedate());
                            $secondweekbeginning  = strtotime('monday next week',$this->geteffectivedate());
                        } else if ($weeks == 2 && $this->GetWeek() == 2) {
                            $firstweekbeginning = strtotime('monday last week',$this->geteffectivedate());
                            $secondweekbeginning = strtotime('monday this week',$this->geteffectivedate());
                        }
                    }
                    
                    $content .= '</div>';
                    $pi = 0;
                    foreach ($day->period as $period)
                    {
                       
                        if($period->attribute('shortname') == null || $period->attribute('shortname') == 'NOPERIOD') {
                          $label = $period->attribute('index');
                        } else {
                          $label = @array_pop(str_split($period->attribute('shortname'),strpos($period->attribute('shortname'),':')+1));  
                        }

                        if(($period->attribute('start') == null) || $CFG->zilink_timetable_display_time == 0) {
                            $content .= '<div class="timetable-days">'.$label.'</div>';
                            $previousperiodtimes['start'][$pi] = null;
                            $previousperiodtimes['end'][$pi] = null;
                        } else {
                         $content .= '<div class="timetable-days">'.$label.'<br>'. $this->GetOffsettedTime($period->attribute('start')). ' - '. $this->GetOffsettedTime($period->attribute('end')) .'</div>';
                         $previousperiodtimes['start'][$pi] = $this->GetOffsettedTime($period->attribute('start'));
                         $previousperiodtimes['end'][$pi] = $this->GetOffsettedTime($period->attribute('end'));
                         
                        }
                        $pi++;
                        
                    }
 
                    $content .= '</div>';
                    
                    if($day->attribute('index') == $firstweekstart)
                    {
                        if($args['ajax_call'])
                          $content = '<div id ="zilinktimetable" style="display: table-row-group;">';
                        else
                          $content .= '<div id ="zilinktimetable" style="display: table-row-group;">';
                    }  
                        
                    if($day->attribute('index') == $days)
                        $weekoffset++; 
                } else {
                    
                    $difftimes = false;
                    
                    if(!empty($previousperiodtimes) && $CFG->zilink_timetable_display_time == 1) {
                        
                        $pi = 0;

                        foreach ($day->period as $period)
                        {
 
                            if($this->GetOffsettedTime($period->attribute('start')) <>  $previousperiodtimes['start'][$pi] || $this->GetOffsettedTime($period->attribute('end')) <>  $previousperiodtimes['end'][$pi]) {
                                $difftimes = true;
                            }
                            
                                $currentperiodtimes['start'][$pi] = $this->GetOffsettedTime($period->attribute('start'));
                                $currentperiodtimes['end'][$pi] = $this->GetOffsettedTime($period->attribute('end'));
                            
                                $pi++;
                        }
                     }

                    $previousperiodtimes =  $currentperiodtimes;
                    
                    if($difftimes) {
                        
                       $content .= '<div class="timetable-days"></div>';
                       foreach ($day->period as $period)
                        { 
                            if($period->attribute('shortname') == null || $period->attribute('shortname') == 'NOPERIOD') {
                                $label = $period->attribute('index');
                            } else {
                                $label = @array_pop(str_split($period->attribute('shortname'),strpos($period->attribute('shortname'),':')+1));  
                            }
                            $content .= '<div class="timetable-days">'.$label.'<br>'. $this->GetOffsettedTime($period->attribute('start')). ' - '. $this->GetOffsettedTime($period->attribute('end')) .'</div>';
                        }
                    }
                }   
                
                    
                $content .= '<div style="display: table-row; width:auto;">';
    
                if($day->attribute('index') > ($days))
                    $dayno =  $day->attribute('index') - ($days);
                else
                    $dayno =  $day->attribute('index');
                    
                $content .= '<div class="timetable-period"><b>'.$dayname[$dayno].'</b></div>';
                
                foreach($day->period as $period)
                {
                    if($weeks == 2) 
                    {
                        if($day->attribute('index') <= $days ) {
                            $startday = $firstweekbeginning;
                            $endday = strtotime('-1 day',$secondweekbeginning);   
                        } else {
                            $startday = $secondweekbeginning;
                            $endday = strtotime('+6 days',$startday);
                        }
                    }else {
                        $startday = $firstweekbeginning;
                        $endday = strtotime('+6 days',$startday);
                    }
 
                    if($this->IsTodayHoliday($startday,$day->attribute('index')))
                    {
                       $content .= '<div class="timetable-period">';
                       $content .= '<div>School Holiday</div>';

                    }
                    elseif(!empty($period->lesson))
                    {
                      
                        foreach($period->lesson as $lesson)
                        { 
                           
                            $current_booking = null;
                            
                            
                            //FIXME need to include classcode when booking
                            
                            if(empty($startday) || empty($endday))
                            {
                                $current_booking = false;
                            }
                            else {
                            
                            $current_booking = $DB->get_record_sql("SELECT * 
                                                FROM {zilink_bookings_rooms}
                                                WHERE date >= ". $startday."
                                                AND date <= ". $endday ."
                                                AND subject = '".$lesson->attribute('subject')."'
                                                AND dayid = ".$day->attribute('index')."
                                                AND periodid = ".$period->attribute('index')."
                                                AND classcode = '".$period->lesson->attribute('shortname')."'
                                                AND status = 1
                                                ORDER BY date"); 
                            }
                                   
                            if($this->IsCurrentPeriod($args['weekoffset'],$day->attribute('index'),$dayname[$dayno],$weeks,$days,$this->GetOffsettedTime($period->attribute('start')),$this->getOffsettedTime($period->attribute('end'))) && $CFG->zilink_timetable_display_time == 1)
                                $content .= '<div class="timetable-period-current">';
                            else
                            {
                                
                                if($current_booking)
                                    $content .= '<div class="timetable-period-room-change">';   
                                else
                                    $content .= '<div class="timetable-period">';
                            }
                            // FIXME check capability
    
                            /*
                            if($mdl_lesson = $DB->get_record('course', array('idnumber'=> $lesson->attribute('refid'))))
                            {
                                if($mdl_lesson->visible == 0 && !$this->person->Security('moodle/course:update'))
                                {
                                    //$content .= '<div>'.$lesson->attribute('subject').'</div>';
                                    if($mdl_cohort = $DB->get_record('cohort', array('idnumber'=> $lesson->attribute('refid'))))
                                    {
                                        if($mdl_cohort_courses = $DB->get_records('enrol', array('customint1' => $mdl_cohort->id, 'enrol' => 'zilink_cohort')))
                                        {
                                            $courses = array();
                                            foreach($mdl_cohort_courses as $mdl_cohort_course)
                                            {
                                                $mdl_course = $DB->get_record('course',array('id' => $mdl_cohort_course->courseid));
                                                
                                                if(is_object($mdl_course))
                                                {
                                                    if($mdl_course->idnumber <> $lesson->attribute('refid') && ($mdl_course->visible == 1 || $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')))
                                                        $courses[] = $mdl_course;
                                                }
                                                
                                            }
                                            if(count($courses) == 1)
                                            {
                                                $course = array_shift($courses);
                                                $content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/view.php?id='.$course->id.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                            }
                                            elseif(count($courses) > 1)
                                            {
                                                $cat = null;
                                                foreach($courses as $course)
                                                {
                                                    if($cat == null)
                                                        $cat = $course->category;
                                                    
                                                    if(!$cat == $course->category)
                                                    {
                                                        $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                                        continue 1;
                                                    }
                                                    $cat = $course->category;
                                                }
                                                if($cat == null)
                                                    $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                                else
                                                {
                                                    $category = $DB->get_record('course_categories',array('id' => $cat));
                                                    if(is_object($category))
                                                    {
                                                        if($category->visible == 1)
                                                            $content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/category.php?id='.$cat.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                                        else
                                                            $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                                    }
                                                    else
                                                    {
                                                        $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                                    }
                                                }
                                            }
                                            else
                                                $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                        }
                                        else
                                            $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                         
                                    }
                                    else
                                        $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                }
                                else
                                { 
                                    $content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/view.php?id='.$mdl_lesson->id.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                }
                            }
                            else
                            {
                             * */
                             
                                if($mdl_cohort = $DB->get_record('cohort', array('idnumber'=> $lesson->attribute('refid'))))
                                {
                                    if($mdl_cohort_courses = $DB->get_records('enrol', array('customint1' => $mdl_cohort->id, 'enrol' => 'zilink_cohort')))
                                    {
                                        $courses = array();
                                        foreach($mdl_cohort_courses as $mdl_cohort_course)
                                        {
                                            $mdl_course = $DB->get_record('course',array('id' => $mdl_cohort_course->courseid));
                                            
                                            if((is_object($mdl_course) && $mdl_course->visible == 1) || (is_object($mdl_course) &&  $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')))
                                                $courses[] = $mdl_course;
                                                
                                        }
                                        if(count($courses) == 1)
                                        {
                                            $course = array_shift($courses);
                                            $content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/view.php?id='.$course->id.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                        }
                                        elseif(count($courses) > 1)
                                        {
                                            $cat = null;
                                            foreach($courses as $course)
                                            {
                                                if($cat == null)
                                                    $cat = $course->category;
                                                
                                                if(!$cat == $course->category)
                                                {
                                                    continue 1;
                                                }
                                            }
                                            
                                            $category = $DB->get_record('course_categories',array('id' => $cat));
                                            if(is_object($category))
                                            {
                                                if((is_object($category) && $category->visible == 1) || (is_object($mdl_course) && $this->person->Security()->IsAllowed('moodle/category:viewhiddencategories')))
                                                {
                                                    //$content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/category.php?id='.$cat.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                                    $content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/index.php?categoryid='.$cat.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                                }
                                                else
                                                    $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                            }
                                            else
                                            {
                                                $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                            }
                                            //$content .=  '<a target="_self" href="'.$this->httpswwwroot.'/course/category.php?id='.$cat.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                        }
                                        else
                                        {
                                            $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                        }
                                    }
                                    else
                                    {
                                        $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                    }
                                }
                                else
                                {
                                    $content .= '<div>'.$lesson->attribute('subject').'</div>';
                                }
                            //}
                                //$content .= '<div>'.$lesson->attribute('shortname').'</div>';
    
                            if($current_booking)
                                $content .= '<div>'.$current_booking->room.'</div>';
                            else 
                            {
                                if(!empty($lesson->rooms))
                                {                       
                                    foreach($lesson->rooms->room as $room)
                                    {
                                        if($CFG->zilink_timetable_room_label == 'description')
                                        {
                                            $content .= '<div>'.$room->attribute('description').'</div>';
                                        }
                                        else
                                        {
                                            $content .= '<div>'.$room->attribute('code').'</div>';
                                        }
                                    }
                                }
                            }
                            
                            if(!$this->person->Security()->IsAllowed('moodle/course:update'))
                            {
                                if(!empty($lesson->teachers))
                                {
                                    foreach($lesson->teachers->teacher as $teacher)
                                    {
                                        if((string)$teacher->attribute('name') <> 'UNKNOWN')
                                            $content .= '<div>'.$teacher->attribute('name').'</div>';
                                    }
                                }
                            }
                            else
                                $content .= '<div>'.$period->lesson->attribute('shortname').'</div>';
                            
                            //$content .= '</div>';
                            
                            if($lesson->Attribute('length') == 1 && count($period->lesson) > 1)
                                break;
                        }   
                         
                    } else {
                        if($this->IsCurrentPeriod($args['weekoffset'],$day->attribute('index'),$dayname[$dayno],$weeks,$days,$this->GetOffsettedTime($period->attribute('start')),$this->GetOffsettedTime($period->attribute('end'))) && $CFG->zilink_timetable_display_time == 1)
                            $content .= '<div class="timetable-period-current">';
                        else
                          $content .= '<div class="timetable-period">';
                    }
   
                    $content .= '</div>';   
                }
                
                $content .= '</div>';
            }
        //    $count++;
        //}
        $content .= '</div>';
        $content .= '</div>';
        return $content;
        
    }
    
    private function GetLegend()
    {
        
        $content = '<div class="timetable_legend">';
        $content .= '<div style="display: inline; width:auto;">';
        $content .=     '<div style="float:left; width:15px; height: 20px; border: 1px solid; background-color: #FFFF00; border-color:#CCCCCC; margin-right: 10px;"></div>';
        $content .=     '<div style="float:left;  width:8em"><p style="vertical-align: middle; margin:0px;">Current Period</p></div>';
        $content .=     '<div style="float:left; width:15px; height: 20px; border: 1px solid; background-color: #000066; border-color:#CCCCCC; margin-right: 10px;"></div>';
        $content .=     '<div style="float:left;  width:8em"><p style="vertical-align: middle; margin:0px;">Room Change</p></div>';
        $content .= '</div>';
        
        return $content;
        
    }
    
    function GetTodaysTimetable($args)
    {
        global $CFG,$DB,$USER,$PAGE;
  
        $args = $this->DefaultArguments(array(   'display_full_timetable' => true,
                                                            'page_layout' => 'base',
                                                            'ajax_call' => false, 
                                                            'user_id' => optional_param('id', 0, PARAM_INT),
                                                            'requested_by' =>'timetable',
                                                            'weekoffset' => 0,
                                                            'user_idnumber' => $USER->idnumber
                                                         ),$args);
        
        $weeks = $this->timetable_number_weeks;
        $days =  $this->timetable_number_days;
        
        $dayname = $this->GetDayNames();
        $currrentweek = $this->GetWeek();
        
        $firstweekstart = 1;
        $secondweekstart = $days +1;
        $weekoffset = $args['weekoffset'];
        
        $count = 0;

        if(!isset($CFG->zilink_timetable_advance_booking))
            $CFG->zilink_timetable_advance_booking = 1;
        
        $currentperiodtimes = array();
        $previousperiodtimes = array();
        
        $firstweekbeginning = null;
        $secondweekbeginning = null;
        $secondweekoffset = 0;
        $gotsecondweek = false;
        
        $data = $this->person->GetPersonData('timetable',$args['user_idnumber'],true);
        
        
        $firstweekstart = 1;
        $secondweekstart = $days +1;

        $table              = new html_table();
        $table->cellpadding = '10px';    
        $table->width       = '95%';
        $table->head        = array(get_string('timetable_start','local_zilink'),get_string('timetable_end','local_zilink'),get_string('timetable_subject','local_zilink'),get_string('timetable_room','local_zilink'),get_string('timetable_teacher','local_zilink'));
        $table->align       = array('center', 'center', 'center', 'center','center');
        $table->border      = '2px'; 
        $table->tablealign  = 'center';
        //$table->attributes['class']   = 'generaltable tableleft';
        
        $cells = array();
        
        foreach ($data->timetable->day as $day)
        {
            
            if(date('N') <= 5)
            {
                if(!in_array($day->attribute('shortname'),array(date('D'),'A'.date('D'),'B'.date('D'),'1'.date('D'),'2'.date('D'),date('D').'A',date('D').'B',date('D').'1',date('D').'2')))
                    continue;
            }
            else
            {
                if(!in_array($day->attribute('shortname'),array('Mon','AMon','BMon','1Mon','2Mon','MonA','MonB','Mon1','Mon2')))
                    continue;
            }   
        
            
            if($day->attribute('index') < $firstweekstart) 
                    continue;
            
                
            if($weeks == 2)
            {
                if($currrentweek == 1 )
                {
                    if($day->attribute('index') >= $secondweekstart)
                        continue;
                }
                elseif($currrentweek == 2 ) 
                {
                    if($day->attribute('index') < $secondweekstart)
                        continue;
                }
            }
            foreach($day->period as $period)
            {
                
                $cells[] = $this->GetOffsettedTime($period->attribute('start'));
                $cells[] = $this->GetOffsettedTime($period->attribute('end'));
                
                if(!empty($period->lesson))
                {   
                    foreach($period->lesson as $lesson)
                    {
                        if($mdl_cohort = $DB->get_record('cohort', array('idnumber'=> $lesson->attribute('refid'))))
                        {
                            if($mdl_cohort_courses = $DB->get_records('enrol', array('customint1' => $mdl_cohort->id, 'enrol' => 'zilink_cohort')))
                            {
                                $courses = array();
                                foreach($mdl_cohort_courses as $mdl_cohort_course)
                                {
                                    $mdl_course = $DB->get_record('course',array('id' => $mdl_cohort_course->courseid));
                                    
                                    if((is_object($mdl_course) && $mdl_course->visible == 1) || (is_object($mdl_course) && $this->person->Security()->IsAllowed('moodle/course:viewhiddencourses')))
                                        $courses[] = $mdl_course;
                                        
                                }
                                if(count($courses) == 1)
                                {
                                    $course = array_shift($courses);
                                    $cells[] = '<a target="_self" href="'.$this->httpswwwroot.'/course/view.php?id='.$course->id.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                }
                                elseif(count($courses) > 1)
                                {
                                    $cat = null;
                                    foreach($courses as $course)
                                    {
                                        if($cat == null)
                                            $cat = $course->category;
                                        
                                        if(!$cat == $course->category)
                                        {
                                            continue 1;
                                        }
                                    }
                                    
                                    $category = $DB->get_record('course_categories',array('id' => $cat));
                                    if(is_object($category))
                                    {
                                        if($category->visible == 1 || $this->person->Security()->IsAllowed('moodle/category:viewhiddencategories'))
                                        {
                                             $cells[] =   '<a target="_self" href="'.$this->httpswwwroot.'/course/index.php?categoryid='.$cat.'"><b>'.$lesson->attribute('subject').'</b></a>';
                                        }
                                        else
                                            $cells[] = $lesson->attribute('subject');
                                    }
                                    else
                                    {
                                        $cells[] = $lesson->attribute('subject');
                                    }
                                }
                                else
                                {
                                    $cells[] = $lesson->attribute('subject');
                                }
                            }
                            else
                            {
                                $cells[] = $lesson->attribute('subject');
                            }
                        }
                        else
                        {
                            $cells[] = $lesson->attribute('subject');
                        }
                        //$cells[] = $lesson->attribute('subject');
                        
                        $current_booking = null;
                        
                        $startday = strtotime('monday this week',$this->geteffectivedate());
                        $endday = strtotime('saturday this week',$this->geteffectivedate());
                        
                        $current_booking = $DB->get_record_sql("SELECT * 
                                                FROM {zilink_bookings_rooms}
                                                WHERE date >= ". $startday."
                                                AND date <= ". $endday ."
                                                AND subject = '".$lesson->attribute('subject')."'
                                                AND dayid = ".$day->attribute('index')."
                                                AND periodid = ".$period->attribute('index')."
                                                AND classcode = '".$period->lesson->attribute('shortname')."'
                                                AND status = 1
                                                ORDER BY date"); 

                        if($current_booking)
                            $cells[] = $current_booking->room;
                        else 
                        {
                            $tmp = '';
                            if(!empty($lesson->rooms))
                            {                       
                                foreach($lesson->rooms->room as $room)
                                {
                                    if($tmp == '')
                                        $tmp .= $room->attribute('code');
                                    else 
                                        $tmp .= '<br>'.$room->attribute('code');
                                }
                                $cells[] = $tmp;
                            }
                            else
                                $cells[] = '';
                        }
                        
                        if(!empty($lesson->teachers))
                        {
                            foreach($lesson->teachers->teacher as $teacher)
                            {
                                if((string)$teacher->attribute('name') <> 'UNKNOWN')
                                    $cells[] = $teacher->attribute('name'); 
                                else {
                                    $cells[] = '';
                                }      
                                  
                            }
                        }
                        else
                            $cells[] = '';
                                                
                        if($lesson->Attribute('length') == 1 && count($period->lesson) > 1)
                            break;
                    }
                }
                else 
                {
                    $cells[] = '';  
                    $cells[] = '';
                    $cells[] = '';
                }
            }
            $count++;
        }
        
        $table->data = array_chunk($cells, 5);
        return html_writer::table($table);  
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
    
    function WhichWeekReportData()
    {
        $data = array();
    
        $count = 0;

        foreach($this->academic_year_weekbeginning as $week =>$days)
        {
            $row = array();
            $row[] = date('d/m/Y',$week);
            
            if($this->GetWeek($week) === false)
            {
                $row[] = 'Holiday';
            }
            else {
                $row[] = $this->GetWeek($week);
            }
            
            $data[] = $row;
        }       
        
        return $data;
            
    }
}