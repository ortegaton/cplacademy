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
require_once(dirname(dirname(__FILE__)). '/lib.php');
require_once($CFG->libdir . '/tablelib.php');


require_login();
$courseid = optional_param('cid',1,PARAM_INTEGER);
$sesskey = required_param('sesskey',PARAM_RAW);
confirm_sesskey($sesskey);

if (!$courseid == SITEID) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = context_system::instance();
    $PAGE->set_context($context);
}


if($CFG->version >= 2011120500) {
    $data = data_submitted();

    if(isset($data->zilink_roombooking_maintenance_booking)) {
       $bookings = $data->zilink_roombooking_maintenance_booking; //optional_param_array('zilink_roombooking_maintenance_booking',0, PARAM_RAW);
    } else {
        $bookings = 0;
    }
        
}
else
    $bookings = optional_param('zilink_roombooking_maintenance_booking',0, PARAM_RAW);
    
$room = optional_param('zilink_bookings_rooms_booked_room','', PARAM_RAW);

$urlparams = array('cid' => $courseid, 'sesskey' => $sesskey);
$PAGE->https_required();
$PAGE->set_url('/local/zilink/plugins/bookings/rooms/maintenance.php', $urlparams);
$PAGE->verify_https_required();
$strmanage = get_string('bookings_rooms_maintenance', 'local_zilink');


$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$PAGE->requires->css('/local/zilink/plugins/timetable/styles.css');
$PAGE->requires->js('/local/zilink/plugins/bookings/module.js');

$tt_url = new moodle_url('/local/zilink/plugins/bookings/rooms/maintenance.php', $urlparams);
$PAGE->navbar->add(get_string('zilink','local_zilink'));
$PAGE->navbar->add(get_string('bookings_rooms_maintenance', 'local_zilink'), $tt_url);
$PAGE->set_pagelayout('base');


$security = new ZiLinkSecurity();

if(!$security->IsAllowed('local/zilink:bookings_rooms_maintenance_manage'))
{
    redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,get_string('requiredpermissionmissing','local_zilink'),1);
}

try {
    
    $roombooking = new ZiLinkBookingManager($courseid);

    if(!empty($bookings) && !empty($room))
    {
        $roombooking->RoomBookingMaintenanceCreateBookings($bookings,$room);
    }
        
    $content = $roombooking->RoomBookingMaintenanceForm($room);
    $content .= $roombooking->RoomBookingMaintenanceCurrentBookings();
    
} catch (Exception $e)  {
    
    
    
    if($CFG->debug == DEBUG_DEVELOPER && is_siteadmin()) {
        
        $message = $e->getMessage();    
        $message .= '<br>';
        $message .= '<pre>'. print_r($e->getTrace(),true).'</pre>';
        redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,$message,3);
         
    }
    else {
        $message = get_string('requireddatamissing','local_zilink');
        redirect($CFG->httpswwwroot.'/course/view.php?id='.$courseid,$message,1);
    }
    
}





$header = $OUTPUT->header();
$footer = $OUTPUT->footer();
echo $header.$content.$footer;
