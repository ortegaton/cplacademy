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
/*
==============================================
    Moodle Required Plugin Text
==============================================
*/

$string['bookings'] = 'Bookings';
$string['bookings_settings'] = $string['bookings'];

$string['bookings_rooms_page_title'] = $string['zilink'] .' '.$string['bookings'] . ' - Rooms';
$string['bookings_rooms'] = $string['bookings'] . ' - Rooms';
$string['bookings_rooms_title_desc'] = 'Use this page to configure the ZiLink Bookings - Rooms.';

$string['bookings_settings_rooms'] = 'Rooms';
$string['bookings_settings_resources'] = 'Resources';

$string['bookings_config'] = 'Configuration';
$string['bookings_maintenance'] = 'Maintenance';
$string['bookings_bookings'] = 'Bookings';

$string['bookings_rooms_mybooking'] = 'My Room Booking';

$string['bookings_nocurrent'] = 'No Current';
$string['bookings_booking'] = $string['bookings'];


/* 
=============================================
    Moodle Permission Text
=============================================
*/

$string['zilink:bookings_rooms_viewown'] = 'Bookings - Rooms - View Own Bookings';
$string['zilink:bookings_rooms_maintenance_manage'] = 'Bookings - Rooms - Manage Maintenace Bookings';
$string['zilink:bookings_rooms_viewalternative'] = 'Bookings - Rooms  - View Link for Alternate Room Booking System';

/*
==============================================
    ZiLink Settings Text
==============================================
*/


//$string['bookings_rooms_weeks_in_advance'] = 'Weeks In Advance';
//$string['bookings_rooms_weeks_in_advance_help'] = 'Select the number of weeks a booking can be made in advance.';

$string['bookings_rooms_bookable_rooms'] = 'Bookable Rooms';
$string['bookings_rooms_bookable_rooms_help'] = 'Select which rooms you want to be bookable.';

$string['bookings_rooms_weeks_in_advance'] = 'Weeks in Advance';
$string['bookings_rooms_weeks_in_advance_desc'] = 'The number of weeks to allow staff to book a room in advance';
$string['bookings_rooms_weeks_in_advance_help'] = '';

//$string['bookings_rooms_allowed_rooms'] = 'Allowed Rooms';
//$string['bookings_rooms_allowed_rooms_desc'] = 'The rooms selected will be available for staff to book.';

$string['bookings_rooms_noroomsallowed'] = 'No rooms are allowed to be booked';
$string['bookings_rooms_no_dates_available'] = 'No dates available to be booked';

$string['bookings_rooms_email_notifications'] = 'Email Notifications';
$string['bookings_rooms_email_notifications_desc'] = 'Enable to have room booking confirmation emails.';

$string['bookings_rooms_alternativelink'] = 'Alternate Link';
$string['bookings_rooms_alternativelink_desc'] = 'If you use an alternative rooming booking systemom, enter the url here.';


/*
==============================================
    ZiLink Room Booking Text
==============================================
*/
$string['bookings_support_desc'] = 'For more information about configuring the ZiLink Timetable please see our ';

$string['bookings_rooms_introduction1'] = 'Please click the <i>Period</i> where you want to change the room.<br>Your current bookings are listed at the bottom of the page. If you wish to cancel a booking, click the <i>Cancel</i> button of the booking you wish to cancel.';
$string['bookings_rooms_introduction2'] = 'Please select an available room from the drop-down list in the <i>Booking Details</i> section and choose an available date(s) in the <i>Available Dates</i> section, then click <i>Book</i>.<br>Greyed-out dates are not available';

$string['bookings_rooms_selectroom'] = 'Please select room to display dates';


$string['bookings_rooms_booking_detail'] = 'Booking Details';
$string['bookings_rooms_avaliable_dates'] = 'Available Dates';

$string['bookings_rooms_system'] = 'System';
$string['bookings_rooms_system_desc'] = 'If you are changing booking system, please save and the configuration setting will be available';

$string['bookings_rooms_schoolbooking_siteid'] = 'Site ID';
$string['bookings_rooms_schoolbooking_siteid_help'] = 'The Site ID is the XXX part of you custom url http://xxx.schoolbooking.com';
/*
==============================================
    ZiLink Room Maintenance Text
==============================================
*/

$string['bookings_rooms_maintenance'] = 'Room Maintenance';
$string['bookings_rooms_maintenance_introductions'] ='Admins can book rooms for maintenance. This booking will make the room unavailable for the room booking system.<br><br>Please select the room, dates and periods you want to reserve.';

$string['bookings_rooms_maintenance_nodatesavailable'] = 'No dates available to be booked';






/*
==============================================
    ZiLink Resource Booking Text
==============================================
*/