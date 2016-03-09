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

require_once( dirname(__FILE__).'/security.php');
require_once( dirname(__FILE__).'/interfaces.php');
require_once( dirname(__FILE__).'/lib.php');
require_once( dirname(dirname(dirname(__FILE__))).'/lib.php');

class ZiLinkBase
{
    
    function __construct($courseid = null,$required = true){
        global $CFG,$DB;
        
        date_default_timezone_set('UTC');
        $this->course = new stdClass();
        $this->course->id = $courseid;
        
        $this->timetable_count = 0;
        $this->timetable_number_weeks = 0;
        $this->timetable_number_days = 0;
        
        $this->academic_year = array('start' => 0, 'end' => 0);  
        $this->data = new ZiLinkData();
        
        
        $this->httpswwwroot = str_replace('http:', 'https:', $CFG->wwwroot);
        $this->person = new ZiLinkPerson();
        
        try {
            $this->LoadTimetableSettings();
            $this->SetupAcademicYear();
            $this->SetupAcademicYearWeeks();
        } catch (Exception $e) {
            if ($required) {
                throw $e;
            }
        }
    }
    
    public function geteffectivedate()
    {
        global $CFG;
        
        if(!isset($CFG->zilink_effective_date))
        {
            $default = get_config(null,'zilink_effective_date');
            if($default)
            {
                $CFG->zilink_effective_date = $default;
            }
            else 
            {
                $CFG->zilink_effective_date  = 0;
                set_config('zilink_effective_date','0');
            }
        }
        
        if(isset($CFG->zilink_effective_date) && $CFG->zilink_effective_date <> 0) {
            return $CFG->zilink_effective_date;
        } else {
            return strtotime('now');
        }
        
    }
    
    protected function LoadTimetableSettings()
    {   
        try {
            
            $timetable = $this->data->GetGlobalData('timetable',true);
            $this->timetable_number_weeks = $timetable->Attribute('weeks');
            $this->timetable_number_days =  $timetable->Attribute('days');
            
        }  catch (Exception $e) {
            
            throw new Exception("Global Timetable Data Missing", 1);
            
        }
    }
    
    protected function GetDayNames()
    {
        global $CFG;
        
        if($CFG->zilink_timetable_startday == 'Sunday') {
            
            return array(   1 => 'Sunday', 
                                2 => 'Monday',
                                3 => 'Tuesday',
                                4 => 'Wednesday',
                                5 => 'Thursday',
                                6 => 'Friday',
                                7 => 'Saturday');  
              
        } 
        
            return  array(   1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                                7 => 'Sunday');    
        
    }
    
    protected function GetOffsettedTime($time) {
        
        global $CFG;
        
        $parts = explode(":",$time);
        if($CFG->zilink_timetable_time_offset > 0 ) {
            $parts[0] = $parts[0] + $CFG->zilink_timetable_time_offset;
        }
        unset($parts[2]);
        $time = implode(':',$parts);
        return $time;
    }
    
    /*
    protected function GetThisWeek()
    {
        global $CFG;
        
        return strtotime($CFG->zilink_timetable_startday .' this week');
    }
 */
    protected function SetupAcademicYear()
    {
        $terminfo = $this->data->GetGlobalData('terminfo',true);
        
        foreach($terminfo->terms->term as $term)
        {
            date_default_timezone_set('UTC');
            $termstart = strtotime($term->Attribute('start'));
            $termend = strtotime($term->Attribute('end'));

            if($this->academic_year['start'] == 0 || $termstart < $this->academic_year['start'])
            {
                $this->academic_year['start'] = $termstart;
            }
            
            if($this->academic_year['end'] < $termend)
            {
                $this->academic_year['end'] = $termend;
            }
        }

        if($this->academic_year['end'] < $this->geteffectivedate())
        {
            throw new Exception("Academic Year Not Defined");
        }
    }
    
    protected function SetupAcademicYearWeeks()
    {
        global $CFG;
        
        $weekbeginning = strtotime($CFG->zilink_timetable_startday ." this week",$this->academic_year['start']);
        
        
        while($weekbeginning < $this->academic_year['end'])
        {
            $currentday = $weekbeginning;
            
            for($i = 1; $i <= $this->timetable_number_days; $i++)
            {
                
                if($this->IsDateInTerm($currentday))
                {
                    $this->academic_year_weekbeginning[$weekbeginning][$currentday]  = 'Y';
                }
                else {
                    $this->academic_year_weekbeginning[$weekbeginning][$currentday]  = 'N';
                }
                
                if($i > 0)
                {
                    $currentday = strtotime('+1 days',$currentday);
                }
            }
            $weekbeginning = strtotime("+7 days",$weekbeginning);
        }
    }   

    protected function IsDateInTerm($timestamp)
    {
        $terminfo = $this->data->GetGlobalData('terminfo',true);
        
        foreach($terminfo->terms->term as $term)
        {
            $termstart = strtotime($term->Attribute('start'));
            
            $termend = strtotime($term->Attribute('end'));
            
            if($timestamp >= $termstart && $timestamp <= $termend)
            {
                
                foreach($term->closures->closure as $closure)
                {
                                        
                    $closurestart = strtotime(str_replace('/', '-', $closure->Attribute('start')));
                    $closureend = strtotime(str_replace('/', '-', $closure->Attribute('end')));
                     
                    if(strstr($closure->Attribute('description'),'Half-Term Holiday') || strstr($closure->Attribute('description'),'Holiday'))
                    {
                        if($timestamp >= $closurestart && $timestamp <= $closureend ) {
                            return false;
                        }
                    }
                }
                
                 
                return true;
            }
        } 
        
        return false;
    }

    protected function GetWeek($timestamp = false)
    {
        global $CFG; 
         
        if($timestamp == false)
        {
            $timestamp = $this->ThisWeek();
        }
        
        $count = 0;
        
        foreach($this->academic_year_weekbeginning as $week =>$days)
        {
            $flag = false;
            foreach($days as $day)
            {
                if($day == 'Y')
                {
                   $flag = true; 
                }
            }
            
            if($flag)
            {
                $count++;
            }
            
            if($week == $timestamp && $flag)
            {
                if($count % 2)
                {
                    if($CFG->zilink_timetable_first_week == 2)
                    {
                        return '2';
                    }
                    return '1';
                }
                else {
                    if($CFG->zilink_timetable_first_week == 2)
                    {
                        return '1';
                    }
                    return '2';
                }
            }
           
        }
        
        return false;
    }
    
    protected function IsTodayHoliday($timestamp = false, $index = 0)
    {
        global $CFG; 
        
        if($index == 0 && !empty($timestamp))
        {
            $index = floor(($timestamp - $this->ThisWeek($timestamp))/(60*60*24));
        }
        
        $timestamp = $this->ThisWeek($timestamp);
        
        $count = 1;
        foreach($this->academic_year_weekbeginning as $week =>$days)
        {
            
            if($week == $timestamp)
            {
                foreach($days as $day)
                {
                    if($count == $index)
                    {
                        if($day == 'N')
                        {
                           return true; 
                        }
                    }
                    $count++;
                }
                
            }
           
        }
        return false;
    }
    
    

    protected function ThisWeek($timestamp = null)
    {
        global $CFG;
       
        if(empty($timestamp))
        {
            return strtotime($CFG->zilink_timetable_startday .' this week',$this->geteffectivedate());
        }
        return strtotime($CFG->zilink_timetable_startday .' this week',$timestamp);
    }
    
    function LookUpDayIndex($date)
    {
        $index = array_search(date('l',$date),$this->GetDayNames());
        if($this->timetable_number_weeks == 2 && $this->GetWeek($this->ThisWeek($date)) == 2)
        {
            $index = $index + $this->timetable_number_days;
        }
        return $index;
    }
    
    
    protected function CurrentWeek($dayid = null)
    {
        global $CFG;
        
        if(empty($dayid))
        {
            return strtotime($CFG->zilink_timetable_startday .' this week',$this->geteffectivedate());
        }
        
        if($this->GetWeek() == 1 && ($dayid <= $this->timetable_number_days))
        {
            return strtotime($CFG->zilink_timetable_startday .' this week',$this->geteffectivedate());
        } else if ($this->GetWeek() == 1 && ($dayid > $this->timetable_number_days)){
            return strtotime($CFG->zilink_timetable_startday .' next week',$this->geteffectivedate());
        }
        else if($this->GetWeek() == 2 && ($dayid <= $this->timetable_number_days))
        {
            return strtotime($CFG->zilink_timetable_startday .' next week',$this->geteffectivedate());
        }
        else
        {
            return strtotime($CFG->zilink_timetable_startday .' this week',$this->geteffectivedate());
        }
        
        
    }


    protected function IsCurrentWeekHoliday($timestamp = false)
    {
        return ($this->GetWeek($timestamp) === false);
    }
    
    protected function IsCurrentDayHoliday($timestamp = false)
    {
        return ($this->GetWeek($timestamp) === false);
    }
    
    
    protected function IsCurrentPeriod($weekoffset, $index,$dayname,$weeks,$days,$startTime=null, $endTime=null)
    {
        global $CFG;
        
        if($this->IsCurrentWeekHoliday() || $CFG->zilink_timetable_display_time == 0)
            return false;
        
        if(($weeks == 1 && $weekoffset > 0) || ($weeks == 2 && $weekoffset > 1))
            return false;
        
       if($weeks == 2 &&  ($this->getWeek()  == 2) && $index <= $days) 
            return false;
       
       if($weeks == 2 && ($this->getWeek()  == 1) && $index > $days) {
            return false;
       }    
        
        
        if (empty($startTime) || empty($endTime) || $dayname <> date('l'))
            return false;
        
        date_default_timezone_set('UTC');
        $now = explode(":", date('H:i:s',strtotime("+".dst_offset_on($this->geteffectivedate()) ." Seconds", strtotime('now',$this->geteffectivedate()))));
        
        $cHour = intval($now[0]);   
        $cMin = intval($now[1]);    
    
        // break up start time
        $start = explode(":",$startTime);
        
        $sHour = intval($start[0]); 
        $sMin = intval($start[1]);  
    
        // brek up end time
        $end = explode(":",$endTime);
        
        $eHour = intval($end[0]);   
        $eMin = intval($end[1]);    
    
        $pass = true;
    
        if($cHour >= $sHour && $cHour <= $eHour) {
            
            if($cHour == $eHour && $cMin > $eMin) {
                return false;   
            }
            
            if($cHour == $sHour && $cMin < $sMin) {
                return false;
            }
        
            return true;

        } else {
            return false;   
        }
    }

    protected function GetLesson($dayid, $periodid)
    {
        
        $mdl_lesson = new stdClass();
        
        //$dayid = $this->GetDayIndex($dayid,null,$weekid);
        $data = $this->person->GetPersonalData('timetable',true);

        try{
            $period = @array_shift($data->timetable->xpath("//day[@index='".$dayid."']//period[@index='".$periodid."']/lesson/.."));

            if(is_object($period)) {
            
                if($period->Attribute('shortname') == null || $period->Attribute('shortname') == 'NOPERIOD') {
                    $mdl_lesson->periodname  = $period->attribute('index');
                } else {
                    $mdl_lesson->periodname  = $periodname =   $period->Attribute('longname');
                }
                
                foreach($period->lesson as $lesson) {
                    
                    $mdl_lesson->subjectname = $lesson->Attribute('subject');
                    $mdl_lesson->classcode = $lesson->Attribute('shortname');
                    foreach($lesson->rooms->room as $room)
                    {
                        $mdl_lesson->roomname  = $room->Attribute('code');
                    } 
                }
            }
        } catch(Exception$e) {
                throw new Exception("Lesson Not Found");
        }
        
        return $mdl_lesson;
    }

    function GetDayIndex($dayindex)
    {
        
        if($this->timetable_number_weeks == 2)
        {
            if($dayindex > $this->timetable_number_days)
            {
                $dayindex = $dayindex - $this->timetable_number_days;
            }
        }
        return $dayindex;
           
    }
/*
    function GetDayIndex($dayindex = null,$timestamp = null, $weekid = null)
    {
                
        global $CFG;
        
        if(empty($dayindex) && empty($timestamp) && empty($weekid))
        {
           return 0;
        }
          
        $dayname = $this->GetDayNames();
        
        if(empty($dayindex) && !empty($timestamp)) {
            $dayindex = array_search(date('l',$timestamp),$dayname);
            
            if($dayname[$dayindex] == 'Saturday' || $dayname[$dayindex] == 'Sunday') {
                return 0;
            }
        }
        
        if($this->timetable_number_weeks == 1){

           if($dayindex > $this->timetable_number_days) {
                return 0;
           } else if($dayname[$dayindex] == 'Saturday' || $dayname[$dayindex] == 'Sunday') {
               return 0;
           }
           
        } else {
            
            if($dayindex > ( $this->timetable_number_days * $this->timetable_number_weeks)) {
                return 0;
            }else {
                
                if(!empty($weekid)){
                    
                    if($weekid == 2 & $dayindex > $this->globaldata->timetable->Attribute('days'))
                        return $dayindex;
        
                    if($this->getWeek($timestamp) == 2 && $weekid == 2) {
            
                    if($dayindex <= $this->timetable_number_days)
                        $dayindex += $this->timetable_number_days;
                    }
                    
                } else {
                    
                    if($dayindex > $this->timetable_number_days) {
                        $dayindex = $dayindex - $this->timetable_number_days;
                    }    
                    if($dayname[$dayindex] == 'Saturday' || $dayname[$dayindex] == 'Sunday') {
                        return 0;
                    }
                }
           }
        }

        return $dayindex;
    }
*/

    function GetRoomAvailableDates($dayid,$periodid,$bookedroom ='')
    {
        global $CFG,$DB;
        
        $lesson = $this->GetLesson($dayid, $periodid);
        $isroomvacant = $this->IsRoomBookable($dayid,$periodid,$bookedroom);
        $bookeddates = $this->GetRoomsCurrentlyBookedDates($dayid,$periodid,$bookedroom);
        //$vacantdates = $this->GetRoomsCurrentlyAvailableDates($dayid,$periodid,$bookedroom);

        
        $content = '<div class="roombooking-table-col" style="width:20%; text-align:center; border:0px">
                        <div class="roombooking-table-period">
                            <div class="roombooking-table-period-row">';    
    
        $dates = array();
        $weekcount = 1;
        foreach($this->academic_year_weekbeginning as $weekbeginning => $days)
        {
            if($weekcount == $this->timetable_number_weeks)
            {
                if($weekbeginning < $this->CurrentWeek($dayid))
                {
                    continue;
                }

                if($weekbeginning > (strtotime('+'.$CFG->zilink_bookings_rooms_weeks_in_advance.' weeks',$this->ThisWeek())))
                {
                    break;
                }
                
                $count = 1;
                $index = $this->GetDayIndex($dayid);
                foreach ($days as $day => $value)
                {
                    if($count < $index)
                    {
                        $count++;
                        continue;
                    }
                    
                    $dates[$day] = $value;
                    break;
                       
                    $count = 1;
                }
                $weekcount = 0;
            }
            $weekcount++;
        }

        
        if(!empty($dates))
        {
            $num = 0;
            foreach($dates as $date => $value)
            {
                if($date > strtotime('- 1 day',$this->geteffectivedate()))
                {
                    $vacantdates = $this->GetRoomsCurrentlyAvailableDates($dayid,$periodid,$bookedroom,$date);
                    if($num == 2)
                    {
                        $content .= '</div><div class="roombooking-table-period-row">';
                        $num = 0;
                    }   
                    
                    if($this->IsTodayHoliday($date) || ($isroomvacant == false && !in_array($date,$vacantdates))) {
                        $disabled = 'disabled';
                        $style = 'background-color:#DCDCDC';
                    }
                    else if((in_array($date,$bookeddates) || ( $lesson->roomname == $bookedroom )))
                    {
                        $disabled = 'disabled';
                        $style = 'background-color:#DCDCDC';
                    }
                    else {
                        $disabled = '';
                        $style ='';
                    }
                    $content .= '<div class="roombooking-table-col" style="text-align:left; '.$style.'"><input type="checkbox" '.$disabled.' name="selecteddates[]" value="'.date('d-m-Y',$date).'" /> '.date('d-m-Y',$date).'</div>';      
                    $num++;
                }
            }
        } else {
            
            $content .= '<div class="roombooking-table" style="width:100%; text-align:center; border:1px" >';
            $content .= get_string('bookings_rooms_no_dates_available','local_zilink'); 
            $content .= '</div>';
        }   
            
                
        $content .= '    </div>   
                        </div>
                    </div>';
        
        return $content;
    }

    function IsRoomBookable($dayid,$periodid,$bookedroom)
    {
        
        if(empty($bookedroom))
        {
            return false;
        }
        
        return is_object(@array_shift($this->data->GetGlobalData('timetable',true)->xpath("//day[@index='".$dayid."']//period[@index='".$periodid."']//room[@code='". $bookedroom."']")));
    }
    
    function GetRoom($dayid,$periodid,$bookedroom)
    {
        return @array_shift($this->data->GetGlobalData('timetable',true)->xpath("//day[@index='".$dayid."']//period[@index='".$periodid."']//room[@code='". $bookedroom."']"));
    }
    
    public function GetRoomsCurrentlyBookedDates($dayid,$periodid,$bookedroom ='')
    {
        global $DB;
        
        if(empty($bookedroom))
        {
            return array();
        }
        
        $current_bookings = $DB->get_records_sql("SELECT * 
                                        FROM {zilink_bookings_rooms}
                                        WHERE date >= ". strtotime('today',$this->geteffectivedate())."
                                        AND dayid = ".$dayid."
                                        AND periodid = ".$periodid."
                                        AND room = '".$bookedroom ."'
                                        AND status > 0");
        $bookeddates = array();
        if(is_array($current_bookings))
        {
            foreach($current_bookings as $current_booking)
            {
                $bookeddates[] = $current_booking->date;
            }
        }
        return $bookeddates;                              
    }
    
    public function GetRoomsCurrentlyAvailableDates($dayid,$periodid,$bookedroom ='',$date = null)
    {
        global $DB;
        
        if(empty($bookedroom))
        {
            return array();
        }
        
        if($date == null)
        {
            $date = strtotime('today',$this->geteffectivedate());
        }
       
        //FIXME: //date should be passed                              
        $vacantroomsdates = $DB->get_records_sql("SELECT * 
                                        FROM {zilink_bookings_rooms}
                                        WHERE date >= ". $date."
                                        AND dayid = ".$dayid."
                                        AND periodid = ".$periodid."
                                        AND room = '".$bookedroom ."'
                                        AND status = 0");
                                        
        $vacantdates = array();
        if(is_array($vacantroomsdates))
        {
            foreach($vacantroomsdates as $vacantroomsdate)
            {
                if(!in_array($vacantroomsdate->date,$vacantdates))
                    $vacantdates[] = $vacantroomsdate->date;
            }
        }
        return $vacantdates;                              
    }
    
    function GetBookableRoomsList($dayid,$periodid,$currentroom)
    {

        global $CFG;
        
        $freerooms = $this->GetTimetabledFreeRooms($dayid,$periodid);
        $bookablerooms = zilinkdeserialise($CFG->zilink_bookings_rooms_allowed_rooms);

        $roomsavailable = array();
        
        foreach($freerooms as $freeroom)
        {
            if(!in_array($freeroom->Attribute('code'),$roomsavailable)) {

                if(isset($bookablerooms[$freeroom->Attribute('code')]) && $bookablerooms[trim($freeroom->Attribute('code'))] == '1')
                {
                    $roomsavailable[] = $freeroom->Attribute('code');
                }
            }
        }
        sort($roomsavailable);
        // /onChange="get_dates(\''.$this->httpswwwroot.'/blocks/zilink/plugins/timetable/dates.php\',\''.$dayid.'\',\''.$periodid.'\',this.form.bookedroom,\'dates\');"
        $content = '<select id="zilink_bookings_rooms_booked_room" name="zilink_bookings_rooms_booked_room">';       
        $content .= '<option selected="selected" value="'.$currentroom.'">'.$currentroom.'</option>';
        
        foreach($roomsavailable as $room)
        {       
            if($currentroom <> $room)
                $content .= '<option value="'.$room.'">'.$room.'</option>';
        }
        
        $content .= '</select>';
    
        return $content;
    }
    
    function GetTimetabledFreeRooms($dayid,$periodid)
    {
        if(empty($dayid) && empty($periodid))
        {
            $ttrooms = $this->data->GetGlobalData('timetable',true)->xpath("//room");
            $rooms = array();
            foreach($ttrooms as $ttroom)
            {
                if(!array_key_exists($ttroom->Attribute('code'),$rooms))
                {
                    $rooms[$ttroom->Attribute('code')] = $ttroom;
                }
            }
            return $rooms; 
        }
        return $this->data->GetGlobalData('timetable',true)->xpath("//day[@index='".$dayid."']//period[@index='".$periodid."']/room");
    }
    
    function GetPeriods()
    {
        return $this->data->GetGlobalData('timetable',true)->xpath("//period");
    }
}
