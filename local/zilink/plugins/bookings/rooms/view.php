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

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$weekid = optional_param('weekid',0,PARAM_INTEGER);
$courseid = required_param('cid',PARAM_INTEGER);
$sesskey = required_param('sesskey',PARAM_RAW);


confirm_sesskey($sesskey);

$dayid = optional_param('day',0, PARAM_INTEGER);
$periodid = optional_param('period',0, PARAM_INTEGER);
$selecteddates = optional_param_array('selecteddates','',PARAM_RAW);
$bookedroom = optional_param('bookedroom','',PARAM_RAW);

if (!$courseid == SITEID) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}

$urlparams = array('cid' => $courseid, 'sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/bookings/rooms/view.php', $urlparams);
$PAGE->verify_https_required();

$strmanage = get_string('bookings_rooms_mybooking', 'local_zilink');

$PAGE->set_pagelayout('base');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/bookings/rooms/styles.css');
$PAGE->requires->css('/local/zilink/plugins/timetable/styles.css');
//$PAGE->requires->js('/blocks/zilink/plugins/timetable/ajax.js');

$tt_url = new moodle_url('/local/zilink/plugins/bookings/rooms/view.php', $urlparams);
$PAGE->navbar->add(get_string('zilink','local_zilink'));
$PAGE->navbar->add(get_string('bookings_rooms_mybooking', 'local_zilink'), $tt_url);

$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:bookings_rooms_viewown'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('no_rights','local_zilink'),1);
}

try {

    $bookings = new ZiLinkBookingManager($courseid);
    
    if(!empty($dayid) && !empty($periodid) && empty($selecteddates) && empty($bookedroom))
    {
       $content = $bookings->RoomBookingBookingForm(array('dayid' => $dayid, 'periodid' => $periodid));     
       
    }
    elseif(!empty($selecteddates) && !empty($bookedroom))
    {
        if($bookings->CreateRoomBooking($dayid,$periodid,$selecteddates,$bookedroom))
        {
            $content =  $bookings->RoomBookingGetTimetable();
            $content .=  $bookings->RoomBookingGetCurrentBookings();
        }
    }
    else
    {
        $content =  $bookings->RoomBookingGetTimetable();
        $content .=  $bookings->RoomBookingGetCurrentBookings();
    }
    
} catch (Exception $e)  {
    
    $message = get_string('requireddatamissing','local_zilink');
    
    if($CFG->debug == DEBUG_DEVELOPER) {
            
        $message .= '<br>';
        $message .= '<pre>'. print_r($e->getTrace(),true).'</pre>';
         
    }
    
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,$message,1);
}

$header = $OUTPUT->header();
$footer = $OUTPUT->footer();
echo $header.$content.$footer;