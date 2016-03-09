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

$string['zilink_guardian_view_settings']            = 'View';
$string['zilink_guardian_view_general_settings']    = 'General';
$string['zilink_guardian_view_attendance_settings'] = 'Attendance';
$string['zilink_guardian_view_assessment_settings'] = 'Assessment';

$string['zilink_guardian_view_assessment_overview_settings'] = 'Overview';
$string['zilink_guardian_view_assessment_subjects_settings'] = 'Subjects';

/* 
=============================================
    Moodle Permission Text
=============================================
*/

$string['zilink:guardian_view_addinstance'] = 'Guardian View - Add Instance';
$string['zilink:guardian_view'] = 'Guardian View - View Guardian View';
$string['zilink:guardian_view_icons'] = 'Guardian View - Show Subject Icons';
$string['zilink:guardian_view_recent'] = 'Guardian View - View Welcome Tab';
$string['zilink:guardian_view_student_details_photo'] = 'Guardian View - Student Details - View Student Photo';
$string['zilink:guardian_view_student_details_achievement'] = 'Guardian View - Student Details - View Achievement Panel';
$string['zilink:guardian_view_student_details_attendance'] = 'Guardian View - Student Details - View Attendance Panel';
$string['zilink:guardian_view_student_details_behaviour'] = 'Guardian View - Student Details - View Behaviour Panel';

$string['zilink:guardian_view_attendance_recent'] = 'Guardian View - Attendance Tab - View Recent Attendance Panel';
$string['zilink:guardian_view_attendance_overview'] = 'Guardian View - Attendance Tab - View Assessment Overview Panel';

$string['zilink:guardian_view_overview_home_learning'] = 'Guardian View - Overview Tab - View Homework Panel';
$string['zilink:guardian_view_subjects'] = 'Guardian View - View Subjects Tab';
$string['zilink:guardian_view_subjects_overview_assessment'] = 'Guardian View - Subjects Tab - View Subject Assessment Overview';
$string['zilink:guardian_view_subjects_teacher_details'] = 'Guardian View - Subjects Tab - View Teacher Information Panel';
$string['zilink:guardian_view_subjects_teacher_details_email'] = 'Guardian View - Subjects Tab - View Teacher Information Panel With Email Addresses';
$string['zilink:guardian_view_subjects_assessment'] = 'Guardian View - Subjects Tab - View Assessment Panel';
$string['zilink:guardian_view_subjects_homework'] = 'Guardian View - Subjects Tab - View Home Learning Panel';
$string['zilink:guardian_view_subjects_submitted_work'] = 'Guardian View - Subjects Tab - View Submitted Work Panel';
$string['zilink:guardian_view_subjects_reports'] = 'Guardian View - Subjects Tab - View Reports Panel';
$string['zilink:guardian_view_homework'] = 'Guardian View - View Homework Tab';
$string['zilink:guardian_view_reports'] = 'Guardian View - View Reports Tab';
$string['zilink:guardian_view_timetable'] = 'Guardian View - View Timetable Tab';
$string['zilink:guardian_view_information'] = 'Guardian View - View Information Tab';
$string['zilink:guardian_view_information_student_address'] = 'Guardian View - Information Tab - View Student Address';

/*
==============================================
    ZiLink Settings Text
==============================================
*/



$string['guradian_view_general_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'] .': General';
$string['guardian_view_general_title']                      = 'General Settings';
$string['guardian_view_general_title_desc']                 = 'TODO';
$string['guardian_view_general_notification_message']       = 'Notification Message';
$string['guardian_view_general_page_set']                   = 'Page Set';
$string['guardian_view_general_pages_display_notification'] = 'Display Notification';
$string['guardian_view_general_allowed_subjects']           = 'Allowed Subjects';


$string['guardian_view_default_student_photo'] = ' ';

$string['guardian_view_default_attendance_page_title']                  = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'] .': Attendance';
$string['guardian_view_default_attendance_pages_display_notification']  = 'Display Notification';
$string['guardian_view_default_attendance_title']                       = 'Attendance Settings';
$string['guardian_view_default_attendance_title_desc']                  = 'TODO';
$string['guardian_view_default_attendance_delay']                       = 'Attendance Delay';
$string['guardian_view_default_attendance_delay_help']                  = 'TODO';       

$string['guardian_view_default_attendance_overview_present_below_trigger'] = 'Below Trigger';
$string['guardian_view_default_attendance_overview_present_below_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_present_below_comment'] = 'Below Comment';
$string['guardian_view_default_attendance_overview_present_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['guardian_view_default_attendance_overview_present_above_trigger'] = 'Above Trigger';
$string['guardian_view_default_attendance_overview_present_above_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_present_above_comment'] = 'Above Comment';
$string['guardian_view_default_attendance_overview_present_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['guardian_view_default_attendance_overview_late_below_trigger'] = 'Below Trigger';
$string['guardian_view_default_attendance_overview_late_below_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_late_below_comment'] = 'Below Comment';
$string['guardian_view_default_attendance_overview_late_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['guardian_view_default_attendance_overview_late_above_trigger'] = 'Above Trigger';
$string['guardian_view_default_attendance_overview_late_above_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_late_above_comment'] = 'Above Comment';
$string['guardian_view_default_attendance_overview_late_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['guardian_view_default_attendance_overview_authorised_absence_below_trigger'] = 'Below Trigger';
$string['guardian_view_default_attendance_overview_authorised_absence_below_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_authorised_absence_below_comment'] = 'Below Comment';
$string['guardian_view_default_attendance_overview_authorised_absence_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['guardian_view_default_attendance_overview_authorised_absence_above_trigger'] = 'Above Trigger';
$string['guardian_view_default_attendance_overview_authorised_absence_above_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_authorised_absence_above_comment'] = 'Above Comment';
$string['guardian_view_default_attendance_overview_authorised_absence_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['guardian_view_default_attendance_overview_unauthorised_absence_below_trigger'] = 'Below Trigger';
$string['guardian_view_default_attendance_overview_unauthorised_absence_below_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_unauthorised_absence_below_comment'] = 'Below Comment';
$string['guardian_view_default_attendance_overview_unauthorised_absence_below_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';
$string['guardian_view_default_attendance_overview_unauthorised_absence_above_trigger'] = 'Above Trigger';
$string['guardian_view_default_attendance_overview_unauthorised_absence_above_trigger_help'] = '';
$string['guardian_view_default_attendance_overview_unauthorised_absence_above_comment'] = 'Above Comment';
$string['guardian_view_default_attendance_overview_unauthorised_absence_above_comment_help'] = 'A comment based upon Attendance data. The Trigger number represents the number of days. Choose the trigger number (number of days) and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['guardian_view_assessment_overview_page_title']                  = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'] .': Assessment Overview';
$string['guardian_view_assessment_overview_title']                       = 'Assessment Overview Settings';
$string['guardian_view_assessment_overview_title_desc']                  = 'TODO';

$string['guardian_view_default_assessment_overview_general_comment'] = 'General Comment';
$string['guardian_view_default_assessment_overview_general_comment_help'] = 'A general comment about the Attainments/Targets being published.';

$string['guardian_view_default_assessment_overview_below_trigger'] = 'Trigger';
$string['guardian_view_default_assessment_overview_below_trigger_help'] = '';
$string['guardian_view_default_assessment_overview_below_comment'] = 'Comment';
$string['guardian_view_default_assessment_overview_below_comment_help'] = 'A comment based upon the number of subjects where the Attainment is below Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached';

$string['guardian_view_default_assessment_overview_level_trigger'] = 'Trigger';
$string['guardian_view_default_assessment_overview_level_trigger_help'] = '';
$string['guardian_view_default_assessment_overview_level_comment'] = 'Comment';
$string['guardian_view_default_assessment_overview_level_comment_help'] = ' A comment based upon the number of subjects where the Attainment is equal to Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached';

$string['guardian_view_default_assessment_overview_above_trigger'] = 'Trigger';
$string['guardian_view_default_assessment_overview_above_trigger_help'] = '';
$string['guardian_view_default_assessment_overview_above_comment'] = 'Comment';
$string['guardian_view_default_assessment_overview_above_comment_help'] = 'A comment based upon the number of subjects where the Attainment is above Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached';

$string['guardian_view_default_assessment_subjects_gerneral_comment'] = 'General Comment';
$string['guardian_view_default_assessment_subjects_gerneral_comment_help'] = 'A general comment about the Attainments/Targets being published in this subject.';

$string['guardian_view_assessment_subjects_page_title']      = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'] .': Assessment Subjects';
$string['guardian_view_assessment_subjects_title']           = 'Assessment Subjects Settings';
$string['guardian_view_assessment_subjects_title_desc']      = 'TODO';

$string['guardian_view_default_assessment_subjects_general_comment'] = 'Comment';
$string['guardian_view_default_assessment_subjects_general_comment_help'] = 'A general comment about the Attainments/Targets being published in this subject.';

$string['guardian_view_default_assessment_subjects_below_trigger'] = 'Trigger';
$string['guardian_view_default_assessment_subjects_below_trigger_help'] = '';
$string['guardian_view_default_assessment_subjects_below_comment'] = 'Comment';
$string['guardian_view_default_assessment_subjects_below_comment_help'] = 'A comment based upon the number of assessments where the Attainment is below Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['guardian_view_default_assessment_subjects_level_trigger'] = 'Trigger';
$string['guardian_view_default_assessment_subjects_level_trigger_help'] = '';
$string['guardian_view_default_assessment_subjects_level_comment'] = 'Comment';
$string['guardian_view_default_assessment_subjects_level_comment_help'] = 'A comment based upon the number of assessments where the Attainment is equal to Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached.';

$string['guardian_view_default_assessment_subjects_above_trigger'] = 'Trigger';
$string['guardian_view_default_assessment_subjects_above_trigger_help'] = '';
$string['guardian_view_default_assessment_subjects_above_comment'] = 'Comment';
$string['guardian_view_default_assessment_subjects_above_comment_help'] = 'A comment based upon the number of assessments where the Attainment is above Target. Choose the trigger number and write the comment. This comment will only be included if the trigger number you select has been reached.';


$string['guardian_view_default_end'] = 'End';
$string['guardian_view_default_mondays_timetable'] = 'Monday\'s Timetable';
$string['guardian_view_default_overview'] = 'Overview';
$string['guardian_view_default_recent_attendance'] = 'Recent Attendance';
$string['guardian_view_default_room'] = 'Room';
$string['guardian_view_default_start'] = 'Start';
$string['guardian_view_default_student_details'] = 'Student Details';
$string['guardian_view_default_student_dob'] = 'Date of Birth';
$string['guardian_view_default_student_name'] = 'Name';
$string['guardian_view_default_student_gender'] = 'Gender';
$string['guardian_view_default_student_house'] = 'House';
$string['guardian_view_default_student_registration_group'] = 'Registration Class';
$string['guardian_view_default_student_year_group'] = 'Year Group';
$string['guardian_view_default_subject'] = 'Subject';
$string['guardian_view_default_teacher'] = 'Teacher';
$string['guardian_view_default_todays_timetable'] = 'Today\'s Timetable';
$string['guardian_view_default_welcome'] = 'Welcome';
$string['guardian_view_default_homelearning'] = 'Home Learning';
$string['guardian_view_default_homelearning_noworkset'] = 'No home learning set for any subject';

$string['guardian_view_default_report'] = 'Reports';
$string['guardian_view_default_pupil_details'] = 'Pupil Details';
$string['guardian_view_deafult_recent_attenandance'] = 'Recent';
$string['guardian_view_default_overview'] = 'Overview';
$string['guardian_view_default_attendance'] = 'Attendance';


$string['guardian_view_default_recent_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'];
$string['guardian_view_default_overview_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'];
$string['guardian_view_default_subjects_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'];
$string['guardian_view_default_information_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'];
$string['guardian_view_default_timetable_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'];
$string['guardian_view_default_report_page_title']                 = $string['zilink'].' '. $string['guardian']. ' ' .$string['zilink_guardian_view_settings'];

$string['guardian_view_default_recent']                 = $string['recent'];
$string['guardian_view_default_overview']                 = $string['overview'];
$string['guardian_view_default_information']                 = $string['information'];
$string['guardian_view_default_timetable']                 = (isset($string['timetable'])) ? $string['timetable']: 'Timetable';
$string['guardian_view_default_subjects']                 = $string['subjects'];


$string['guardian_view_default_current_work'] = '{$a} activities';
$string['guardian_view_default_current_work_desc'] = 'This is a list of activites that {$a->firstname} has been allocated together with their due dates.';
$string['guardian_view_default_current_work_assigned'] = 'Assigned';
$string['guardian_view_default_current_work_non_assigned'] = 'No work currently assigned';
$string['guardian_view_default_current_work_attempted'] = 'Attempted';
$string['guardian_view_default_current_work_non_attempted'] = 'No work currently assigned has been attempted'; 
$string['guardian_view_default_current_work_overdue'] = 'Overdue';
$string['guardian_view_default_current_work_non_overdue'] = 'No work currently overdue'; 

$string['guardian_view_default_submitted_work'] = 'Submitted Work';
$string['guardian_view_default_submitted_work_desc'] = 'This is a list of previously submitted activites that {$a->firstname} has submitted together with the date.';

$string['guardian_view_default_submitted_work_comment'] = 'Comment';
$string['guardian_view_default_submitted_work_module_type'] = 'Type';

$string['guardian_view_default_subject_reports'] = 'Subject Reports';

$string['guardian_view_default_no_reports_published'] = ' No  reports have been published. Please check back soon.'; 


$string['zilink_guardian_view_default_homework_settings']  = $string['homework'];

$string['guardian_view_default_datedue'] = 'Due Date';

$string['guardian_view_default_no_homework'] = 'No Outstanding Homework.';

$string['guardian_view_default_homework'] = $string['homework']; 

$string['guardian_view_default_homework_detail'] = 'Homework Detail';

$string['guardian_view_default_simple'] = 'Simple';
$string['guardian_view_default_advanced'] = 'Advanced';

$string['guardian_view_default_homework_display_duedate'] = 'Display Due Dates';

$string['guardian_view_default_homework_status'] = 'Status';

$string['guardian_view_default_recent_behaviour'] = 'Behaviour';
$string['guardian_view_default_recent_achievement'] = 'Achievement';

/*
==============================================
    ZiLink Panels Text
==============================================
*/

if(file_exists($CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces/'.$CFG->zilink_guardian_view_interface.'/lang/custom_en.php'))
{
    include($CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces/'.$CFG->zilink_guardian_view_interface.'/lang/custom_en.php');
}