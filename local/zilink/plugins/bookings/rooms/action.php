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
 
define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../../../config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

$action = required_param('action',PARAM_RAW);
$bookings = new ZiLinkBookingManager();
$security = new ZiLinkSecurity();

switch ($action)
{

    case "bookings_rooms_available_dates_update":
      
        $dayid = optional_param('day',0, PARAM_INTEGER);
        $periodid = optional_param('period',0, PARAM_INTEGER);
        $room = optional_param('room',0, PARAM_RAW);
        
        echo $bookings->GetRoomAvailableDates($dayid,$periodid,$room);
        
        break;
    case "bookings_rooms_cancel_booking":
        if($security->IsAllowed('local/zilink:bookings_rooms_viewown'))
        {
            $id = optional_param('id',0, PARAM_INTEGER);
            $bookings->RoomBookingCancelBooking($id);
            echo $bookings->RoomBookingGetCurrentBookings(true);
        }
        break;
    case 'roombooking_room_maintenance_form_update':
        $room = required_param('room',PARAM_RAW);
        echo $bookings->RoomBookingMaintenanceDatesAndPeriods($room);
        break;
    case 'roombooking_maintenance_cancel_booking':
        if($security->IsAllowed('local/zilink:bookings_rooms_maintenance_manage'))
        {
            $id = required_param('bookingid',PARAM_INT);
            $bookings->RoomBookingMaintenanceCancelBookings($id);
            echo $bookings->RoomBookingMaintenanceCurrentBookings(true);
        }
        break;
    default:
        echo 'default';
          break;
}