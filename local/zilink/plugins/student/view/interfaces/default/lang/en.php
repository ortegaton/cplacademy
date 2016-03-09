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


$string['zilink_student_view_settings']            = 'View';
$string['zilink_student_view_general_settings']    = 'General';

/* 
=============================================
    Moodle Permission Text
=============================================
*/

$string['zilink:student_view_addinstance'] = 'Student View - Add Instance';
$string['zilink:student_view'] = 'Student View - View student View';
$string['zilink:student_view_attendance'] = 'Student View - Attendance Tab';
$string['zilink:student_view_assessment'] = 'Student View - Assessment Tab';

/*
==============================================
    ZiLink Settings Text
==============================================
*/


$string['student_view_assessment'] = 'Assessment';
$string['student_view_attendance'] = 'Attendance';


$string['student_view_general_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];
$string['student_view_general_title']                      = 'General Settings';
$string['student_view_general_title_desc']                 = 'TODO';
$string['student_view_general_notification_message']       = 'Notification Message';
$string['student_view_general_page_set']                   = 'Page Set';
$string['student_view_general_pages_display_notification'] = 'Display Notification';
$string['student_view_general_allowed_subjects']           = 'Allowed Subjects';

$string['student_view_attendance_page_title']                  = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'] .': Attendance';
$string['student_view_attendance_pages_display_notification']  = 'Display Notification';
$string['student_view_attendance_title']                       = 'Attendance Settings';
$string['student_view_attendance_title_desc']                  = 'TODO';
$string['student_view_attendance_delay']                       = 'Attendance Delay';
$string['student_view_attendance_delay_help']                  = 'TODO';       

$string['student_view_default_student_photo'] = ' ';


$string['student_view_default_attendance_overview_present_below_trigger'] = 'Below Trigger';
$string['student_view_default_attendance_overview_present_below_trigger_help'] = '';
$string['student_view_default_attendance_overview_present_below_comment'] = 'Below Comment';
$string['student_view_default_attendance_overview_present_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['student_view_default_attendance_overview_present_above_trigger'] = 'Above Trigger';
$string['student_view_default_attendance_overview_present_above_trigger_help'] = '';
$string['student_view_default_attendance_overview_present_above_comment'] = 'Above Comment';
$string['student_view_default_attendance_overview_present_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['student_view_default_attendance_overview_late_below_trigger'] = 'Below Trigger';
$string['student_view_default_attendance_overview_late_below_trigger_help'] = '';
$string['student_view_default_attendance_overview_late_below_comment'] = 'Below Comment';
$string['student_view_default_attendance_overview_late_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['student_view_default_attendance_overview_late_above_trigger'] = 'Above Trigger';
$string['student_view_default_attendance_overview_late_above_trigger_help'] = '';
$string['student_view_default_attendance_overview_late_above_comment'] = 'Above Comment';
$string['student_view_default_attendance_overview_late_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['student_view_default_attendance_overview_authorised_absence_below_trigger'] = 'Below Trigger';
$string['student_view_default_attendance_overview_authorised_absence_below_trigger_help'] = '';
$string['student_view_default_attendance_overview_authorised_absence_below_comment'] = 'Below Comment';
$string['student_view_default_attendance_overview_authorised_absence_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['student_view_default_attendance_overview_authorised_absence_above_trigger'] = 'Above Trigger';
$string['student_view_default_attendance_overview_authorised_absence_above_trigger_help'] = '';
$string['student_view_default_attendance_overview_authorised_absence_above_comment'] = 'Above Comment';
$string['student_view_default_attendance_overview_authorised_absence_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['student_view_default_attendance_overview_unauthorised_absence_below_trigger'] = 'Below Trigger';
$string['student_view_default_attendance_overview_unauthorised_absence_below_trigger_help'] = '';
$string['student_view_default_attendance_overview_unauthorised_absence_below_comment'] = 'Below Comment';
$string['student_view_default_attendance_overview_unauthorised_absence_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['student_view_default_attendance_overview_unauthorised_absence_above_trigger'] = 'Above Trigger';
$string['student_view_default_attendance_overview_unauthorised_absence_above_trigger_help'] = '';
$string['student_view_default_attendance_overview_unauthorised_absence_above_comment'] = 'Above Comment';
$string['student_view_default_attendance_overview_unauthorised_absence_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['student_view_assessment_overview_page_title']                  = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'] .': Assessment Overview';
$string['student_view_assessment_overview_title']                       = 'Assessment Overview Settings';
$string['student_view_assessment_overview_title_desc']                  = 'TODO';

$string['student_view_default_assessment_overview_general_comment'] = 'General Comment';
$string['student_view_default_assessment_overview_general_comment_help'] = 'A general comment about the Attainments/Targets being published.';

$string['student_view_default_assessment_overview_below_trigger'] = 'Trigger';
$string['student_view_default_assessment_overview_below_trigger_help'] = '';
$string['student_view_default_assessment_overview_below_comment'] = 'Comment';
$string['student_view_default_assessment_overview_below_comment_help'] = 'A comment based upon the number of subjects where the Attainment is below Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached';

$string['student_view_default_assessment_overview_level_trigger'] = 'Trigger';
$string['student_view_default_assessment_overview_level_trigger_help'] = '';
$string['student_view_default_assessment_overview_level_comment'] = 'Comment';
$string['student_view_default_assessment_overview_level_comment_help'] = ' A comment based upon the number of subjects where the Attainment is equal to Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached';

$string['student_view_default_assessment_overview_above_trigger'] = 'Trigger';
$string['student_view_default_assessment_overview_above_trigger_help'] = '';
$string['student_view_default_assessment_overview_above_comment'] = 'Comment';
$string['student_view_default_assessment_overview_above_comment_help'] = 'A comment based upon the number of subjects where the Attainment is above Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached';

$string['student_view_default_assessment_subjects_gerneral_comment'] = 'General Comment';
$string['student_view_default_assessment_subjects_gerneral_comment_help'] = 'A general comment about the Attainments/Targets being published in this subject.';

$string['student_view_assessment_subjects_page_title']      = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'] .': Assessment Subjects';
$string['student_view_assessment_subjects_title']           = 'Assessment Subjects Settings';
$string['student_view_assessment_subjects_title_desc']      = 'TODO';

$string['student_view_default_assessment_subjects_general_comment'] = 'Comment';
$string['student_view_default_assessment_subjects_general_comment_help'] = 'A general comment about the Attainments/Targets being published in this subject.';

$string['student_view_default_assessment_subjects_below_trigger'] = 'Trigger';
$string['student_view_default_assessment_subjects_below_trigger_help'] = '';
$string['student_view_default_assessment_subjects_below_comment'] = 'Comment';
$string['student_view_default_assessment_subjects_below_comment_help'] = 'A comment based upon the number of assessments where the Attainment is below Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['student_view_default_assessment_subjects_level_trigger'] = 'Trigger';
$string['student_view_default_assessment_subjects_level_trigger_help'] = '';
$string['student_view_default_assessment_subjects_level_comment'] = 'Comment';
$string['student_view_default_assessment_subjects_level_comment_help'] = 'A comment based upon the number of assessments where the Attainment is equal to Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['student_view_default_assessment_subjects_above_trigger'] = 'Trigger';
$string['student_view_default_assessment_subjects_above_trigger_help'] = '';
$string['student_view_default_assessment_subjects_above_comment'] = 'Comment';
$string['student_view_default_assessment_subjects_above_comment_help'] = 'A comment based upon the number of assessments where the Attainment is above Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['student_view_default_end'] = 'End';
$string['student_view_default_mondays_timetable'] = 'Monday\'s Timetable';
$string['student_view_default_overview'] = 'Overview';
$string['student_view_default_recent_attendance'] = 'Recent Attendance';
$string['student_view_default_room'] = 'Room';
$string['student_view_default_start'] = 'Start';
$string['student_view_default_student_details'] = 'Student Details';
$string['student_view_default_student_dob'] = 'Date of Birth';
$string['student_view_default_student_name'] = 'Name';
$string['student_view_default_student_gender'] = 'Gender';
$string['student_view_default_student_house'] = 'House';
$string['student_view_default_student_registration_group'] = 'Registration Class';
$string['student_view_default_student_year_group'] = 'Year Group';
$string['student_view_default_subject'] = 'Subject';
$string['student_view_default_teacher'] = 'Teacher';
$string['student_view_default_todays_timetable'] = 'Today\'s Timetable';
$string['student_view_default_welcome'] = 'Welcome';
$string['student_view_default_homelearning'] = 'Home Learning';
$string['student_view_default_homelearning_noworkset'] = 'No home learning set for any subject';

$string['student_view_default_report'] = 'Reports';
$string['student_view_default_recent'] = 'Recent';
$string['student_view_deafult_recent_attenandance'] = 'Recent';
$string['student_view_default_overview'] = 'Overview';


$string['student_view_default_recent_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];
$string['student_view_default_overview_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];
$string['student_view_default_subjects_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];
$string['student_view_default_information_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];
$string['student_view_default_timetable_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];
$string['student_view_default_report_page_title']                 = $string['zilink'].' '. $string['student']. ' ' .$string['zilink_student_view_settings'];

$string['student_view_default_recent']                 = $string['recent'];
$string['student_view_default_overview']                 = $string['overview'];
$string['student_view_default_information']                 = $string['information'];
$string['student_view_default_timetable']                 = (isset($string['timetable'])) ? $string['timetable']: 'Timetable';
$string['student_view_default_subjects']                 = $string['subjects'];


$string['student_view_default_current_work'] = '{$a} activities';
$string['student_view_default_current_work_desc'] = 'This is a list of activites that {$a->firstname} has been allocated together with their due dates.';
$string['student_view_default_current_work_assigned'] = 'Assigned';
$string['student_view_default_current_work_non_assigned'] = 'No work currently assigned';
$string['student_view_default_current_work_attempted'] = 'Attempted';
$string['student_view_default_current_work_non_attempted'] = 'No work currently assigned has been attempted'; 
$string['student_view_default_current_work_overdue'] = 'Overdue';
$string['student_view_default_current_work_non_overdue'] = 'No work currently overdue'; 

$string['student_view_default_submitted_work'] = 'Submitted activities';
$string['student_view_default_submitted_work_desc'] = 'This is a list of previous activites that {$a->firstname} has submitted together with the date.';

$string['student_view_default_subject_reports'] = 'Subject Reports';

if(file_exists($CFG->dirroot.'/local/zilink/plugins/student/view/interfaces/'.$CFG->zilink_student_view_interface.'/lang/custom_en.php'))
{
    include($CFG->dirroot.'/local/zilink/plugins/student/view/interfaces/'.$CFG->zilink_student_view_interface.'/lang/custom_en.php');
}
