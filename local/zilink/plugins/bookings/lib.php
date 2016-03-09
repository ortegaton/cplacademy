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

class ZiLinkBookingManager  extends ZiLinkBase {
    
    function __construct($courseid = null){
        
        parent::__construct($courseid);
        
    }
    
    public function RoomBookingGetTimetable()
    {
        global $CFG,$DB,$USER,$OUTPUT;
                
        $currrentweek = $this->GetWeek();
        $displayedweek = 1;
        $full = true;
        $tt = $this->person->GetPersonalData('timetable');
        
        $dayname = $this->GetDayNames();
        
        $firstweekstart = 1;
        $secondweekstart = $this->timetable_number_days +1;

        $content = '<div style="float:right; display: inline; width:100%; margin-right:10%;">';
        $content .= '<div style="float:right;  width:10em">';
        $content .= $OUTPUT->single_button(new moodle_url('/course/view.php', array('id' => $this->course->id)), get_string('timetable_back','local_zilink'),null,array('width' => '10em'));
        $content .= '</div>';
        
        if($this->person->Security()->IsAllowed('local/zilink:timetable_viewown'))
        {
            $content .= '<div style="float:right;  width:10em">';
            $content .= $OUTPUT->single_button(new moodle_url($this->httpswwwroot.'/local/zilink/plugins/timetable/view.php', array('cid' => $this->course->id, 'sesskey' => sesskey())), get_string('timetable','local_zilink'),null,array('style' => 'width: 10em;'));
            $content .= '</div>';
        }
        $content .= '</div>';
        $content .= '<div class="clearer"></div>';
        $content .= '<div class="timetable">';
        $content .= '<p style="margin-left:20px; margin-top:10px;">'.get_string('bookings_rooms_introduction1','local_zilink').'</p>';
        $content .= '</div>';
                             
        $content .= '<div class="timetable">';
        $count = 0;
         
        foreach ($tt->timetable->day as $day)
        {
            if($full == false && $ttnode->Attribute('weeks') == 2)
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
            
            if($day->attribute('index') > $this->timetable_number_days)
                $displayedweek = 2;
            
            if($day->attribute('index') == $firstweekstart || $day->attribute('index') == $secondweekstart )
            {
                $content .= '<div style="display: table-row; width:auto;">';
                
                if(!isset($CFG->zilink_timetable_week_format) || ($CFG->zilink_timetable_week_format == 0) || $this->timetable_number_weeks == 1)
                    $content .= '<div class="timetable-days" ></div>';
                else
                {
                    if($CFG->zilink_timetable_week_format == 1)
                    {
                        if($day->attribute('index') == $firstweekstart)
                            $content .= '<div class="timetable-days" >Week 1</div>';
                        elseif($day->attribute('index') == $secondweekstart)
                            $content .= '<div class="timetable-days" >Week 2</div>';
                    }
                    elseif($CFG->zilink_timetable_week_format == 2)
                    {
                        if($day->attribute('index') == $firstweekstart)
                            $content .= '<div class="timetable-days" >Week A</div>';
                        elseif($day->attribute('index') == $secondweekstart)
                            $content .= '<div class="timetable-days" >Week B</div>';
                    }
                }
                $pi = 0;
                foreach ($day->period as $period)
                {
                   
                    if($period->attribute('shortname') == null || $period->attribute('shortname') == 'NOPERIOD') {
                      $label = $period->attribute('index');
                    } else {
                      $label = @array_pop(str_split($period->attribute('shortname'),strpos($period->attribute('shortname'),':')+1));  
                    }
           
                    if($period->attribute('start') == null) {
                        $content .= '<div class="timetable-days">'.$label.'</div>';
                        $previousperiodtimes['start'][$pi] = null;
                        $previousperiodtimes['end'][$pi] = null;
                    } else {
                     $content .= '<div class="timetable-days">'.$label.'<br>'. $this->GetOffsettedTime($period->attribute('start')). ' - '. $this->GetOffsettedTime($period->attribute('end')) .'</div>';
                     $previousperiodtimes['start'][$pi] = $this->GetOffsettedTime($period->attribute('start'));
                     $previousperiodtimes['end'][$pi] = $this->GetOffsettedTime($period->attribute('end'));
                     
                     $pi++;
                    }
                    
                }
                $content .= '</div>';
                } else {
                    
                    if(!empty($previousperiodtimes) ) {
                        
                        $pi = 0;
                        $difftimes = false;
                        foreach ($day->period as $period)
                        {
 
                            if($this->GetOffsettedTime($period->attribute('start')) <>  $previousperiodtimes['start'][$pi] || $this->GetOffsettedTime($period->attribute('end')) <>  $previousperiodtimes['end'][$pi])
                            $difftimes = true;
                        
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

            if($day->attribute('index') > ($this->timetable_number_days))
                $dayno =  $day->attribute('index') - ($this->timetable_number_days);
            else
                $dayno =  $day->attribute('index');
                
            $content .= '<div class="timetable-period"><b>'.$dayname[$dayno].'</b></div>';
            
            foreach($day->period as $period)
            {
                
                if(!empty($period->lesson))
                {   
                    foreach($period->lesson as $lesson)
                    {
                        
                        if(!isset($lesson->rooms->room)) {
                            $content .= '<div class="timetable-period">';
                            $content .= '</div>';
                            continue;
                        }
                            
                        $content .= '<div class="timetable-period-bookable"  onClick="parent.location=\''.$CFG->wwwroot.'/local/zilink/plugins/bookings/rooms/bookings.php?cid='.$this->course->id.'&weekid='.$displayedweek.'&day='.$day->attribute('index').'&period='.$period->attribute('index').'&room='.$lesson->rooms->room->attribute('code').'&sesskey='.sesskey().'\'">';

                        $content .=  '<div>'.$lesson->attribute('subject').'</div>';
                        if(!empty($lesson->rooms))
                        {                       
                            foreach($lesson->rooms->room as $room)
                            {
                                $content .= '<div>'.$room->attribute('code').'</div>';
                            }
                        }
                        $content .= '<div>'.$period->lesson->attribute('shortname').'</div>';
                        $content .= '</div>';
                        
                        if($lesson->Attribute('length') == 1 && count($period->lesson) > 1)
                            break;
                    }   
                }   
                else 
                {
                    $content .= '<div class="timetable-period">';
                    $content .= '</div>';
                }
                    
            }
            
            $content .= '</div>';
        }
        $content .= '</div>';
        return $content;
    }

    function RoomBookingGetCurrentBookings($refresh = false)
    {
        global $CFG,$DB,$USER;
    
        
        $current_bookings = $DB->get_records_sql("SELECT * 
                                            FROM {zilink_bookings_rooms}
                                            WHERE date >= ". $this->geteffectivedate()."
                                            AND userid = {$USER->id}
                                            AND status = 1
                                            ORDER BY date");        
                                                                   
        $content = '';
        
        if(!$refresh)
        {
            $this->RoomBookingLoadCancelBookingJavaScript();
            $content .= '<div id="zilink_bookings_rooms_current_bookings" name="zilink_bookings_rooms_current_bookings">';
            
        }
        $content .= '<div id="zilink_bookings_rooms_current_bookings_container" name="zilink_bookings_rooms_current_bookings_container">';
        $content .= '<div class="timetable">
                        <div class="roombooking-table-row">
                            <div class="roombooking-table-col-header" style="width:100%;">Current Bookings</div>
                        </div>
                        <div>';
        $num = 0;
        if($current_bookings)
        {
            foreach($current_bookings as $current_booking)
            {
                if($current_booking->subject == 'Maintenance')
                    continue;
                
                $lesson = $this->GetLesson($current_booking->dayid,$current_booking->periodid);
                //$period = @array_shift($this->person->GetPersonalData('timetable')->xpath("//day[@index='".$current_booking->dayid."']//period[@index='".$current_booking->periodid. "']/lesson/.."));
                
                if($lesson == null) {
                    
                    $DB->delete_records('zilink_bookings_rooms',array('id' => $current_booking->id));
                       continue;
                }
                /*
                $periodname =   $period->Attribute('longname');
                foreach($period->lesson as $lesson)
                {
                    $classcode = $lesson->Attribute('fullname');
                    $subjectname = $lesson->Attribute('subject');
                    foreach($lesson->rooms->room as $room)
                    {
                        $roomname  = $room->Attribute('code');
                    } 
                }
                */
                
               
                if($num == 6)
                {
                    $content .= '</div><div>';
                    $num = 0;
                }
                $content .= '   <div class="timetable-period-booking">
                                    <div class="timetable-period-subject">'.date('d-m-Y',$current_booking->date).'</div>
                                    <div class="timetable-period-subject">'.$lesson->periodname.'</div>
                                    <div class="timetable-period-subject">'.$lesson->subjectname.'</div>
                                    <div class="timetable-period-subject">'.$lesson->classcode.'</div>
                                    <div class="timetable-period-subject">'.$lesson->roomname .' >> '.$current_booking->room.'</div>
                                    <div class="timetable-period-subject"><input type="button" name="'.$current_booking->id.'" value="Cancel" ></div>
                                </div>';
                $num++;
                
            }
        }
        else
        {
                $content .= '   <div class="timetable-period-booking">
                                    <div class="timetable-period-subject"></div>
                                    <div class="timetable-period-subject"></div>
                                    <div class="timetable-period-subject">'.get_string('bookings_nocurrent','local_zilink').'</div>
                                    <div class="timetable-period-subject">'.get_string('bookings_booking','local_zilink').'</div>
                                    <div class="timetable-period-subject"></div>
                                    <div class="timetable-period-subject"></div>
                                </div>';
                $num++;
        }
        
        $content .= '</div></div></div>';
        
        if(!$refresh)
            $content .= '</div>';
        
        return $content;
    }

    function RoomBookingBookingForm($args)//$dayid, $periodid, $weekid)
    {
        
        
        global $CFG,$COURSE,$OUTPUT,$PAGE;

        $this->RoomBookingBookingLoadJavaScript($args);
        $data = new ZiLinkData();
        
        $lesson = $this->GetLesson($args['dayid'], $args['periodid']);

        $content = '<div style="float:right; display: inline; width:100%; margin-right:10%">';
        $content .= '<div style="float:right;  width:10em">';
        $content .= $OUTPUT->single_button(new moodle_url('/course/view.php',array('id' => $this->course->id)), get_string('return_to_course','local_zilink'));
        $content .= '</div>';
        
        if($this->person->Security()->IsAllowed('local/zilink:timetable_viewown'))
        {
            $content .= '<div style="float:right;  width:10em">'.$OUTPUT->single_button(new moodle_url('/local/zilink/plugins/timetable/view.php',array('cid' => $this->course->id)), get_string('timetable','local_zilink')).'</div>';
            //$content .= '<div style="float:right;  width:10em"><input style="width:10em" type="button" value="Timetable" onClick="window.location.href=\'view.php?cid='.$this->course->id.'&sesskey='.sesskey().'\'"> </div>';
        }
        $content .= '<div style="float:right;  width:10em">';
        $content .= $OUTPUT->single_button(new moodle_url('/local/zilink/plugins/bookings/rooms/view.php',array('cid' => $this->course->id)), get_string('back','local_zilink'));
        $content .= '</div>';

        $content .= '</div>';
        $content .= '<div class="clearer"></div>';
        $content .= '<div class="timetable">';
        $content .= '<p style="margin-left:20px; margin-top:10px;">'.get_string('bookings_rooms_introduction2','local_zilink').'</p>';
        $content .= '</div>';
        
        $content .= '<form method="post">
                    <div class="roombooking-table">
                        <div class="roombooking-table-row">
                            <div class="roombooking-table-col-header" style="width:20%;">'.get_string('bookings_rooms_booking_detail','local_zilink').'</div>
                            <div class="roombooking-table-col-header" style="width:40%;">'.get_string('bookings_rooms_avaliable_dates','local_zilink').'</div>
                            <div class="roombooking-table-col-header" style="width:5%;"></div>
                        </div>
                        <div class="roombooking-table-row">
                            <div class="roombooking-table-col" style="width:20%; text-align:center;">
                                <div class="roombooking-table-period">
                                    <div class="roombooking-table-period-row"><div class="roombooking-table-period-col">'.$lesson->periodname.'</div></div>
                                    <div class="roombooking-table-period-row"><div class="roombooking-table-period-col">'.$lesson->subjectname.'</div></div>
                                    <div class="roombooking-table-period-row"><div class="roombooking-table-period-col">'.$lesson->classcode.'</div></div>
                                    <div class="roombooking-table-period-row"><div class="roombooking-table-period-col">'.$this->GetBookableRoomsList($args['dayid'],$args['periodid'],$lesson->roomname)
                                    . html_writer::empty_tag('img', array('id' => 'zilink_timetableupdateprogress', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/loading_small', 'moodle'),'alt' => get_string('timetable_loading', 'local_zilink')))
                                    . html_writer::empty_tag('img', array('id' => 'zilink_timetableupdatefailed', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/cross_red_big', 'moodle'),'alt' => get_string('timetable_updatefailed', 'local_zilink')))
                                    . html_writer::empty_tag('img', array('id' => 'zilink_timetableupdatesuccess', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/tick_green_big', 'moodle'),'alt' => get_string('timetable_updatesuccess', 'local_zilink')))
                                    .' </div>                                    
                                    </div>
                                </div>
                            </div>
                            <div id="zilink_bookings_rooms_available_dates" class="roombooking-table-col" style="width:40%; text-align:center;">'.$this->GetRoomAvailableDates($args['dayid'],$args['periodid'],$lesson->roomname).' </div>
                            
                            <div class="roombooking-table-col" style="width:5%; vertical-align:middle;">
                                <input type="hidden" name="period" value="'.$args['periodid'].'"/>
                                <input type="hidden" name="day" value="'.$args['dayid'].'"/>
                                <input type="submit" value="Book" style="height: 100px; width:75px;"/>
                            </div>
                        </div>
                    </div>
                    </form>';
        /*
         * 
         * <div id="dates">'.get_room_dates($data,$room,$day_id,$period_id).'</div>
         */
        return $content;
        
    } 

    private function RoomBookingBookingLoadJavaScript($args)
    {
        global $PAGE;
        
        $jsdata = array($this->httpswwwroot,$args['dayid'], $args['periodid'],sesskey());
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_timetable',
                        'fullpath'  =>  '/local/zilink/plugins/bookings/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

        $PAGE->requires->js_init_call('M.local_zilink_bookings_room_dates.init', $jsdata, false, $jsmodule);
        
    }

    private function RoomBookingLoadCancelBookingJavaScript()
    {
        global $PAGE;
        
        $jsdata = array($this->httpswwwroot,sesskey());
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_timetable',
                        'fullpath'  =>  '/local/zilink/plugins/bookings/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );
        
        $PAGE->requires->js_init_call('M.local_zilink_bookings_room_cancel_booking.init', $jsdata, false, $jsmodule);
        
    }

    
    function RoomBookingCreateBooking($dayid,$periodid,$selecteddates,$bookedroom)
    {
        global $DB,$USER,$CFG;
        
        $lesson = $this->GetLesson($dayid, $periodid);
    
        $vacantroom = $this->GetRoom($dayid, $periodid,$bookedroom);

        foreach($selecteddates as $selecteddate)
        {
            $selecteddate = strtotime($selecteddate);
            
            $set_as_free = $DB->get_records_sql("SELECT * 
                                    FROM {zilink_bookings_rooms}
                                    WHERE date = ". $selecteddate."
                                    AND dayid = ".$dayid."
                                    AND periodid = ".$periodid."
                                    AND room = '".$lesson->roomname ."'
                                    AND status = 0");
            
            $booked = $DB->get_records_sql("SELECT * 
                                    FROM {zilink_bookings_rooms}
                                    WHERE date = ". $selecteddate."
                                    AND dayid = ".$dayid."
                                    AND periodid = ".$periodid."
                                    AND room = '".$bookedroom ."'
                                    AND status > 0");

            $check = false; 

            if(empty($vacantroom))
            {
                if(!empty($set_as_free))
                    $check = true;
            }
            else
            {
                if(empty($booked))
                    $check = true;
            }

            if($check)
            {
                
                $booking = new stdClass;
                $booking->userid = $USER->id;
                $booking->date = $selecteddate;
                $booking->subject = '';
                $booking->classcode = '';
                $booking->dayid = $dayid;
                $booking->periodid = $periodid;
                $booking->room = $lesson->roomname;
                $booking->status = '0';
                
                $DB->insert_record('zilink_bookings_rooms',$booking);
                
                $booking = new stdClass;
                $booking->userid = $USER->id;
                $booking->subject = $lesson->subjectname;
                $booking->classcode = $lesson->classcode;
                $booking->date = $selecteddate;
                $booking->dayid = $dayid;
                $booking->periodid = $periodid;
                $booking->room = $bookedroom;
                $booking->status = '1';
                
                $DB->insert_record('zilink_bookings_rooms',$booking);
                
                if($CFG->zilink_bookings_rooms_email_notifications == 1) {
                    
                    $site = $DB->get_record('course',array('id' => SITEID));
                    $postsubject = $site->fullname ." - Room Booking Confirmation";
                    
                    $posttext  = $site->fullname ." - Room Booking Confirmation<br>";
                    $posttext .= "<br>";
                    $posttext .= "---------------------------------------------------------------------<br>";
                    $posttext .= date('d-m-Y',$selecteddate) . ' : '. $period->Attribute('longname').' : '. $lesson->classcode . " move from room ".$lesson->roomname. " to " .$bookedroom." has been BOOKED<br>";
                    $posttext .= "---------------------------------------------------------------------<br>";
    
                    $eventdata = new stdClass();
                
                    $eventdata->userfrom         = $DB->get_record('user',array('id' => '2'));
                    $eventdata->userto           = $USER;
                    $eventdata->subject          = $postsubject;
                    $eventdata->fullmessage      = $posttext;
                    $eventdata->fullmessageformat = FORMAT_HTML;
                    $eventdata->fullmessagehtml = $posttext;
                    $eventdata->smallmessage    = '';
                    
                    $eventdata->name            = 'zilink_bookings_rooms_email_notification';
                    $eventdata->component       = 'local_zilink';
                    $eventdata->notification    = 1;
    
                    message_send($eventdata);
                }
            }
        }
        return true;
    }

    function RoomBookingCancelBooking($id)
    {
        global $DB,$USER,$CFG;
        
        
        $booking = $DB->get_record('zilink_bookings_rooms',array('id' => $id));

        
        if($booking)
        {
            $DB->delete_records('zilink_bookings_rooms',array('id'=>$id));
                
            $lesson = $this->GetLesson($booking->dayid,$booking->periodid);
        
            $is_free = $DB->get_record_sql("SELECT * 
                                                FROM {zilink_bookings_rooms}
                                                WHERE date = ". $booking->date ."
                                                AND dayid = ".$booking->dayid."
                                                AND periodid = ".$booking->periodid."
                                                AND room = '".$lesson->roomname."'
                                                AND status = 0");
        
            if(is_object($is_free))
            {
                $DB->delete_records('zilink_bookings_rooms',array('id' => $is_free->id));
            }
    
            if($CFG->zilink_bookings_rooms_email_notifications == 1) {
                
                $site = $DB->get_record('course',array('id' => SITEID)); 
                $postsubject = $site->fullname ." - Room Booking Cancellation";
                
                $posttext  = $site->fullname ." - Room Booking Cancellation<br>";
                $posttext .= "<br>";
                $posttext .= "---------------------------------------------------------------------<br>";
                $posttext .= date('d-m-Y',$booking->date) . ' : '.$period->Attribute('longname').' : '. $booking->classcode . " move from room ".$lesson->roomname. " to " .$booking->room." has been CANCELLED.<br>";
                $posttext .= "---------------------------------------------------------------------<br>";
    
                $eventdata = new stdClass();
            
                $eventdata->userfrom         = $DB->get_record('user',array('id' => '2'));
                $eventdata->userto           = $USER;
                $eventdata->subject          = $postsubject;
                $eventdata->fullmessage      = $posttext;
                $eventdata->fullmessageformat = FORMAT_HTML;
                $eventdata->fullmessagehtml  = $posttext;
                $eventdata->smallmessage     = '';
                
                $eventdata->name             = 'zilink_bookings_rooms_email_notification';
                $eventdata->component        = 'local_zilink';
                $eventdata->notification     = 1;

                message_send($eventdata);
            }
        }
    }
    
    function RoomBookingMaintenanceCreateBookings($bookings,$room) {
        
        global $DB,$USER;
        
        foreach($bookings as $date => $periods)
        {
            
            foreach($periods as $period => $tmp) {
                
                if(!$DB->record_exists('zilink_bookings_rooms', array('subject' => 'Maintenance', 'room' => $room, 'status' =>1, 'periodid' => $period, 'date' => $date, 'dayid' => $this->LookUpDayIndex($date))))
                {
                    $booking = new stdClass;
                    $booking->userid = $USER->id;
                    $booking->subject = 'Maintenance';
                    $booking->room = $room;
                    $booking->status = '1';
                    $booking->periodid = $period;
                    $booking->date = $date;
                    $booking->dayid = $this->LookUpDayIndex($date);

                    $DB->insert_record('zilink_bookings_rooms',$booking);
                }
            }
        }
    } 

    function RoomBookingMaintenanceForm($currentroom = '') {
        
        global $CFG,$COURSE,$PAGE,$OUTPUT;

        $content = '';
       
        $jsdata = array(sesskey());
        
        $jsmodule = array(
                        'name'  =>  'local_zilink_roombooking_maintenance',
                        'fullpath'  =>  '/local/zilink/plugins/bookings/module.js',
                        'requires'  =>  array('base', 'node', 'io')
                    );

        $PAGE->requires->js_init_call('M.local_zilink_bookings_rooms_maintenance.init', $jsdata, false, $jsmodule);
        $PAGE->requires->js_init_call('M.local_zilink_bookings_rooms_maintenance_cancel_booking.init', $jsdata, false, $jsmodule);

        
        $content = '<div style="float:right; display: inline; width:100%; margin-right:10%">';
        $content .= '<div style="float:right;  width:10em">';
        $content .= $OUTPUT->single_button(new moodle_url('/course/view.php',array('id' => $this->course->id)), get_string('return_to_course','local_zilink'));
        $content .='</div>';
        
        //$content .= '<div style="float:right;  width:10em"><input style="width:10em" type="button" name="Cancel" id="Cancel" value="Back" style="width:75px;" onclick="window.location =\'bookings.php?cid='.$this->course->id.'&instanceid='.$this->instance->id.'&sesskey='.sesskey().'\'" /></div>';

        $content .= '</div>';
        $content .= '<div class="clearer"></div>';
        $content .= '<div class="timetable">';
        $content .= '<p style="margin-left:20px; margin-top:10px;">'.get_string('bookings_rooms_maintenance_introductions','local_zilink').'</p>';
        $content .= '</div>';
        
        $content .= '<form method="post">
                    <div class="roombooking-table">
                        <div class="roombooking-table-row">
                            <div class="roombooking-table-col-header" style="width:20%;">Room</div>
                            <div class="roombooking-table-col-header" style="width:40%;">Date & Periods</div>
                            <div class="roombooking-table-col-header" style="width:5%;"></div>
                        </div>
                        <div class="roombooking-table-row">
                            <div class="roombooking-table-col" style="width:20%; text-align:center;">
                                <div class="roombooking-table-period">';

        $list = $this->GetBookableRoomsList(null,null,$currentroom);                    
        if(!empty($list)) {
            $content .= '<div class="roombooking-table-period-row"><div class="roombooking-table-period-col">'.$this->GetBookableRoomsList(null,null,$currentroom);
            $progressparams = array('id' => 'zilink_timetableupdateprogress', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/loading_small', 'moodle'),'alt' => get_string('timetable_loading', 'local_zilink'));
            $content .= html_writer::empty_tag('img', $progressparams);
            $progressparams = array('id' => 'zilink_timetableupdatefailed', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/cross_red_big', 'moodle'),'alt' => get_string('timetable_updatefailed', 'local_zilink'));
            $content .= html_writer::empty_tag('img', $progressparams);
            $progressparams = array('id' => 'zilink_timetableupdatesuccess', 'class' => 'zilink_timetableupdateprogress','src' => $PAGE->theme->pix_url('i/tick_green_big', 'moodle'),'alt' => get_string('timetable_updatesuccess', 'local_zilink'));
            $content .= html_writer::empty_tag('img', $progressparams);
            $content .= '</div></div>';
        } else {
            $content .= get_string('bookings_rooms_noroomsallowed','block_zilink');
        }
                                    
        $content .= '                           
                                    <div class="roombooking-table-period-row"><div class="roombooking-table-period-col"></div></div>
                                    <div class="roombooking-table-period-row"><div class="roombooking-table-period-col">'.'</div></div>
                                    
                                    
                                </div>
                            </div>  
                            
                            <div class="roombooking-table-col" style="width:20%; text-align:center;"><div id="zilink_roombooking_room_maintenance" name="zilink_roombooking_room_maintenance">'.$this->RoomBookingMaintenanceDatesAndPeriods($currentroom).'</div></div>
                            
                            <div class="roombooking-table-col" style="width:5%; vertical-align:middle;">
                                        <input type="submit" value="Book" style="height: 100px; width:75px;"/>
                            </div>
                                
                        </div>
                    </div>
                    </form>';
        
        return $content;
    }

    function RoomBookingMaintenanceDatesAndPeriods($currentroom)
    {
        global $CFG,$DB;
        
        $content = '';
        
        if(empty($currentroom))
        {
            $content = '<div class="roombooking-table" style="width:100%; text-align:center; border:1px">';
            $content .= get_string('bookings_rooms_selectroom','local_zilink'); 
            $content .= '</div>';
            return $content;
        }
        
        $dates = $this->RoomBookingMaintenanceDates();
        if(!empty($dates))
        {
            $content = '<div class="roombooking-table" style="width:75%; text-align:center; border:1px">';

            $content .= '<div class="roombooking-table-period-row">
                         <div class="roombooking-table-col" style="width:20%; text-align:center;">'.get_string('date').'</div>
                         <div class="roombooking-table-col" style="width:70px; text-align:center;">'.get_string('week').'</div>';
            
            $periods = array();
            foreach ($this->GetPeriods() as $period) {
                
                if(!in_array($period->attribute('index'),$periods)) {
                    $periods[] = $period->attribute('index');
                }
                
            }
            
            foreach($periods as $period)
                $content .= '<div class="roombooking-table-col" style="width:5%; text-align:center;">'.$period.'</div>';
            
            $content .= '</div>';
            
            foreach($dates as $date => $value) {
            
                if($date < strtotime('-1 day',$this->geteffectivedate()))
                    continue;
                    
                $content .= '<div class="roombooking-table-period-row"><div class="roombooking-table-col" style="width:20%; text-align:center;">'.date('d/m/Y',$date). '</div>
                            <div class="roombooking-table-col" style="width:20%; text-align:center;">';
                            
                $week =  $this->GetWeek($this->ThisWeek($date));
                if($week)
                {
                    $content .= $week;
                }
                else{
                    $content .= get_string('holiday','local_zilink');
                }
                $content .= '</div>';
                
                $dayindex = $this->LookUpDayIndex($date);
                
                foreach($periods as $period)
                {
                    
                    $current_booking = $DB->get_record_sql("SELECT * FROM {zilink_bookings_rooms} 
                                                                            WHERE date = ". $date."
                                                                            AND dayid = ".$dayindex."   
                                                                            AND periodid = ".$period."
                                                                            AND room = '".$currentroom."'
                                                                            AND status > 0");               
                                                                            
                    $is_free =  $DB->get_record_sql("SELECT * FROM {zilink_bookings_rooms} 
                                                                            WHERE date = ". $date."
                                                                            AND dayid = ".$dayindex."   
                                                                            AND periodid = ".$period."
                                                                            AND room = '".$currentroom."'
                                                                            AND status = 0");                       
                                                                               
                    if((!$this->IsRoomBookable($dayindex, $period, $currentroom) && empty($is_free)) || !empty($current_booking) || $this->IsTodayHoliday($date,$dayindex) || ($value == 'N'))
                    {
                        $disabled = 'disabled';
                        $style = 'background-color:#DCDCDC';
                        
                        if (!empty($current_booking)) {
                            $user = $DB->get_record('user',array('id' => $current_booking->userid ));
                            $title = 'title="'.fullname($user) .' - '.$current_booking->subject .'"';
                        }
                        else
                            $title = '';
                    }
                    else
                    {
                        $disabled = $style = $title = '';
                    }
                    $content .= '<div class="roombooking-table-col" style="width:50px; text-align:center; '.$style.'"><input type="checkbox" value="1" '.$disabled.' name="zilink_roombooking_maintenance_booking['.$date.']['.$period.']" value=""  '.$title.' ></div>';
                }   
                $content .= '</div>';
            }
            $content .= '</div>';
        } else {
            $content = '<div class="roombooking-table" style="width:100%; text-align:center; border:1px">';
            $content .= get_string('bookings_rooms_maintenance_nodatesavailable','local_zilink'); 
            $content .= '</div>';
        }
        
        return $content;
        
    }

    function RoomBookingMaintenanceDates()
    {
        global $CFG;
        
        foreach($this->academic_year_weekbeginning as $weekbeginning => $days)
        {
            if($weekbeginning < $this->ThisWeek())
            {
                continue;
            }

            if($weekbeginning > (strtotime('+'.$CFG->zilink_bookings_rooms_weeks_in_advance.' weeks',$this->ThisWeek())))
            {
                break;
            }
            
            foreach ($days as $day => $value)
            {
                $dates[$day] = $value;
            }
        }

        return $dates;
    }
    
    function RoomBookingMaintenanceCurrentBookings($refresh = false)
    {
        global $CFG,$DB,$USER;
    
        
        $current_bookings = $DB->get_records_sql("SELECT * 
                                            FROM {zilink_bookings_rooms}
                                            WHERE date >= ". strtotime(date('d-m-Y'),$this->geteffectivedate())."
                                            AND subject = 'Maintenance'
                                            AND status = 1
                                            ORDER BY date");
        
        $content = '';
        
        //if($error)
        //  $content .= '<div style="width:100%;"><p class="error"><b>'.$error.'<b></p></div>';
        
        if(!$refresh)
            $content .= '<div id="zilink_bookings_rooms_maintenance_current_bookings">';
        //<div style="display: table-row; width:auto;">';
        
        $content .= '<div id="zilink_bookings_rooms_maintenance_current_bookings_container" name="zilink_bookings_rooms_maintenance_current_bookings_container">';
        $content .= '<div class="timetable">
                        <div class="roombooking-table-row">
                            <div class="roombooking-table-col-header" style="width:100%;">Current Maintenance Bookings</div>
                        </div>
                        <div>';
        $num = 0;
        if($current_bookings)
        {
            foreach($current_bookings as $current_booking)
            {
                                    
                //$period = array_shift($this->data->timetable->xpath("//day[@index='".$current_booking->dayid."']//period[@index='".$current_booking->periodid. "']"));
                
                if($num == 6)
                {
                    $content .= '</div><div>';
                    $num = 0;
                }
                $content .= '   <div class="timetable-period-booking">
                                    <div class="timetable-period-subject">'.date('d-m-Y',$current_booking->date).'</div>
                                    <div class="timetable-period-subject">'.$current_booking->room.'</div>
                                    <div class="timetable-period-subject">Period '.$current_booking->periodid.'</div> 
                                    <div class="timetable-period-subject"><input type="button" value="Cancel" id="zilink_roombooking_cancel" name="'.$current_booking->id.'" alt="'.$current_booking->id.'"></div>
                                </div>';
                $num++;
            }
        }
        else
        {
                $content .= '   <div class="timetable-period-booking">
                                    <div class="timetable-period-subject"></div>
                                    <div class="timetable-period-subject"></div>
                                    <div class="timetable-period-subject">No Current</div>
                                    <div class="timetable-period-subject">Bookings</div>
                                    <div class="timetable-period-subject"></div>
                                    <div class="timetable-period-subject"></div>
                                </div>';
                $num++;
        }
        
        $content .= '</div></div></div>';
        
        if(!$refresh)
            $content .= '</div>';
        
        return $content;
    }
    
    function RoomBookingMaintenanceCancelBookings($id)
    {
        global $DB;

        if($DB->record_exists('zilink_bookings_rooms',array('id' => $id)))
        {
            $DB->delete_records('zilink_bookings_rooms',array('id'=>$id));
            
        }
    }
    
    
    
}