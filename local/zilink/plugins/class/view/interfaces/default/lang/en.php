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
 * Defines the capabilities for the ZiLink local
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/*
==============================================
    Moodle Required Plugin Text
==============================================
*/


$string['zilink_class_view_settings']            = 'View';
$string['zilink_class_view_general_settings']    = 'General';

/* 
=============================================
    Moodle Permission Text
=============================================
*/

$string['zilink:class_view_addinstance'] = 'Class View - Add Instance';
$string['zilink:class_view'] = 'Class View - View Class View';
$string['zilink:class_view_attendance'] = 'Class View - Attendance Tab';
$string['zilink:class_view_assessment'] = 'Class View - Assessment Tab';

/*
==============================================
    ZiLink Settings Text
==============================================
*/

$string['class_view']            = 'Class View';

$string['class_view_assessment'] = 'Assessment';
$string['class_view_attendance'] = 'Attendance';


$string['class_view_general_page_title']                 = $string['zilink'].' '. $string['class']. ' ' .$string['zilink_class_view_settings'];
$string['class_view_general_title']                      = 'General Settings';
$string['class_view_general_title_desc']                 = 'TODO';
$string['class_view_general_notification_message']       = 'Notification Message';
$string['class_view_general_page_set']                   = 'Page Set';
$string['class_view_general_pages_display_notification'] = 'Display Notification';
$string['class_view_general_allowed_subjects']           = 'Allowed Subjects';

$string['class_view_default_attendance_page_title']                  = $string['zilink'].' '. $string['class']. ' ' .$string['zilink_class_view_settings'];
$string['class_view_default_attendance_pages_display_notification']  = 'Display Notification';
$string['class_view_default_attendance_title']                       = 'Attendance Settings';
$string['class_view_default_attendance_title_desc']                  = 'TODO';
$string['class_view_default_attendance_delay']                       = 'Attendance Delay';
$string['class_view_default_attendance_delay_help']                  = 'TODO';       

$string['class_view_default_submitted_work'] = 'Submitted Work';
$string['class_view_default_submitted_work_desc'] = 'This is a list of previously submitted activites that {$a->firstname} has submitted together with the date.';

$string['class_view_default_current_work'] = '{$a} activities';
$string['class_view_default_current_work_desc'] = 'This is a list of activites that {$a->firstname} has been allocated together with their due dates.';
$string['class_view_default_current_work_assigned'] = 'Assigned';
$string['class_view_default_current_work_non_assigned'] = 'No work currently assigned';
$string['class_view_default_current_work_attempted'] = 'Attempted';
$string['class_view_default_current_work_non_attempted'] = 'No work currently assigned has been attempted'; 
$string['class_view_default_current_work_overdue'] = 'Overdue';
$string['class_view_default_current_work_non_overdue'] = 'No work currently overdue'; 

$string['class_view_default_attendance_page_title']                  = $string['zilink'].' '. $string['class']. ' ' .$string['zilink_class_view_settings'] .': Attendance';
$string['class_view_default_attendance_pages_display_notification']  = 'Display Notification';
$string['class_view_default_attendance_title']                       = 'Attendance Settings';
$string['class_view_default_attendance_title_desc']                  = 'TODO';
$string['class_view_default_attendance_delay']                       = 'Attendance Delay';
$string['class_view_default_attendance_delay_help']                  = 'TODO';       

$string['class_view_default_attendance_overview_present_below_trigger'] = 'Below Trigger';
$string['class_view_default_attendance_overview_present_below_trigger_help'] = '';
$string['class_view_default_attendance_overview_present_below_comment'] = 'Below Comment';
$string['class_view_default_attendance_overview_present_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['class_view_default_attendance_overview_present_above_trigger'] = 'Above Trigger';
$string['class_view_default_attendance_overview_present_above_trigger_help'] = '';
$string['class_view_default_attendance_overview_present_above_comment'] = 'Above Comment';
$string['class_view_default_attendance_overview_present_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['class_view_default_attendance_overview_late_below_trigger'] = 'Below Trigger';
$string['class_view_default_attendance_overview_late_below_trigger_help'] = '';
$string['class_view_default_attendance_overview_late_below_comment'] = 'Below Comment';
$string['class_view_default_attendance_overview_late_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['class_view_default_attendance_overview_late_above_trigger'] = 'Above Trigger';
$string['class_view_default_attendance_overview_late_above_trigger_help'] = '';
$string['class_view_default_attendance_overview_late_above_comment'] = 'Above Comment';
$string['class_view_default_attendance_overview_late_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['class_view_default_attendance_overview_authorised_absence_below_trigger'] = 'Below Trigger';
$string['class_view_default_attendance_overview_authorised_absence_below_trigger_help'] = '';
$string['class_view_default_attendance_overview_authorised_absence_below_comment'] = 'Below Comment';
$string['class_view_default_attendance_overview_authorised_absence_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['class_view_default_attendance_overview_authorised_absence_above_trigger'] = 'Above Trigger';
$string['class_view_default_attendance_overview_authorised_absence_above_trigger_help'] = '';
$string['class_view_default_attendance_overview_authorised_absence_above_comment'] = 'Above Comment';
$string['class_view_default_attendance_overview_authorised_absence_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['class_view_default_attendance_overview_unauthorised_absence_below_trigger'] = 'Below Trigger';
$string['class_view_default_attendance_overview_unauthorised_absence_below_trigger_help'] = '';
$string['class_view_default_attendance_overview_unauthorised_absence_below_comment'] = 'Below Comment';
$string['class_view_default_attendance_overview_unauthorised_absence_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['class_view_default_attendance_overview_unauthorised_absence_above_trigger'] = 'Above Trigger';
$string['class_view_default_attendance_overview_unauthorised_absence_above_trigger_help'] = '';
$string['class_view_default_attendance_overview_unauthorised_absence_above_comment'] = 'Above Comment';
$string['class_view_default_attendance_overview_unauthorised_absence_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';




 /*
==============================================
    ZiLink Panels Text
==============================================
*/

if(file_exists($CFG->dirroot.'/local/zilink/plugins/class/view/interfaces/'.$CFG->zilink_class_view_interface.'/lang/custom_en.php'))
{
    include($CFG->dirroot.'/local/zilink/plugins/class/view/interfaces/'.$CFG->zilink_class_view_interface.'/lang/custom_en.php');
}
