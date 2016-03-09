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

$string['guardian_scheduler'] = 'Guardian Scheduler';

$string['zilink:guardian_scheduler_addinstance'] = 'Guardian Scheduler - Add Instance';
$string['zilink:guardian_scheduler_manage'] = 'Guardian Scheduler - Manage';
$string['zilink:guardian_scheduler_viewall'] = 'Guardian Scheduler - View All';
$string['zilink:guardian_scheduler_cancel'] = 'Guardian Scheduler - Cancel';

$string['zilink:guardian_scheduler_book'] = 'Book Guardian Session Appointments';

$string['zilink_guardian_scheduler_settings']                    = 'Scheduler';
$string['zilink_guardian_scheduler_manage_settings']             = 'Manage';
$string['zilink_guardian_scheduler_create_settings']             = 'Create Session';
$string['zilink_guardian_scheduler_teachers_settings']           = 'Teachers';

$string['guardian_scheduler_schedule_title'] = 'Teacher Schedule';
$string['guardian_scheduler_schedule_title_desc'] = 'It can be difficult to ensure that parents/guardians have an opportunity to meet with their child\'s teachers such as at Parent Evenings. You can create a session (e.g. a Parent Evening) using Scheduler that has a start and end time, and identifies the length of each appointment. The teachers involved are added to the Session. Parents can book and appointment from within Guardian View and teachers can see their schedule.';

$string['guardian_scheduler_manage'] = 'Manage';
$string['guardian_scheduler_page_title'] = $string['zilink'] . ' '. $string['guardian_scheduler'];
$string['guardian_scheduler_manage_title_desc'] = 'It can be difficult to ensure that parents/guardians have an opportunity to meet with their child\'s teachers such as at Parent Evenings. You can create a session (e.g. a Parent Evening) using Scheduler that has a start and end time, and identifies the length of each appointment. The teachers involved are added to the Session. Parents can book and appointment from within Guardian View and teachers can see their schedule.';

$string['guardian_scheduler_edit_title'] = 'Edit Session';
$string['guardian_scheduler_edit_title_desc'] = 'It can be difficult to ensure that parents/guardians have an opportunity to meet with their child\'s teachers such as at Parent Evenings. You can create a session (e.g. a Parent Evening) using Scheduler that has a start and end time, and identifies the length of each appointment. The teachers involved are added to the Session. Parents can book and appointment from within Guardian View and teachers can see their schedule.';

$string['guardian_scheduler_teachers_title'] = 'Manage Session Teachers';
$string['guardian_scheduler_teachers_title_desc'] = 'It can be difficult to ensure that parents/guardians have an opportunity to meet with their child\'s teachers such as at Parent Evenings. You can create a session (e.g. a Parent Evening) using Scheduler that has a start and end time, and identifies the length of each appointment. The teachers involved are added to the Session. Parents can book and appointment from within Guardian View and teachers can see their schedule.';

$string['guardian_scheduler_createnew'] = 'Guardian Session Details';
$string['guardian_scheduler_choosedots'] = 'None';

$string['guardian_scheduler_teachers'] = 'Teachers';
$string['guardian_scheduler_session'] = 'Session';

$string['guardian_scheduler_support_desc'] = 'More information about ZiLink Guardian Scheduler is available on our ';
$string['guardian_scheduler_delete'] = 'You have requested to delete the Guardian Scheduler session starting at {$a->time} on {$a->date}';

$string['guardian_scheduler_multiple_children'] = 'Mutliple Students';
$string['guardian_scheduler_select_child'] = 'Select Child';




$string['guardian_scheduler_allschedules'] = 'Show All Appointments';
$string['guardian_scheduler_allowanon_explain'] = 'If this setting is checked, anyone who knows the URL can book an appointment (so parents don\'t have to have an account). Otherwise, only users with :book can access the page.';

$string['guardian_scheduler_appbooked'] = 'Your Appointments Have Been Booked';
$string['guardian_scheduler_appupdated'] = 'Your Appointments Have Been Updated';
$string['guardian_scheduler_apptime'] = 'Appointment time';
$string['guardian_scheduler_appointmentcancel'] = 'You have requested to cancel an appointment with {$a->teacher} at {$a->time} on {$a->date}.';
$string['guardian_scheduler_appointmentlength'] = 'Appointment length /min';
$string['guardian_scheduler_appointmentlengthzero'] = 'The parent\'s scheduler selected has no Appointment Length';
$string['guardian_scheduler_backtoappointments'] = 'Back to Appointments';
$string['guardian_scheduler_blockname'] = 'Parents\' scheduler';
$string['guardian_scheduler_bookapps'] = 'Book Guardian Appointments';
$string['guardian_scheduler_book'] = 'Book Appointments';
$string['guardian_scheduler_busy'] = 'Busy';

$string['guardian_scheduler_config'] = 'Configure';
$string['guardian_scheduler_confirmapps'] = 'Confirm Appointments (without this your appointments will not be booked)';
$string['guardian_scheduler_confirmcancel'] = 'Are you sure you want to cancel this appointment? This cannot be undone!';
$string['guardian_scheduler_confirmdelete'] = 'Are you sure you want to delete this Parents\' scheduler? Clicking "Yes" will also delete the appointments for that Parents\' scheduler, and this cannot be undone!';
$string['guardian_scheduler_date'] = 'Date';
$string['guardian_scheduler_emptyschedule'] = 'There are currently no appointments booked';
$string['guardian_scheduler_manageteachers'] = 'Manage Teachers';
$string['guardian_scheduler_fail'] = '{$a} appointments have failed to be made';
$string['guardian_scheduler_formfailed'] = 'Unfortunately, the booking form is experiencing problems at the moment.';
$string['guardian_scheduler_importteachers'] = 'Import teachers from';
$string['guardian_scheduler_iealternatively'] = 'Alternatively, ';
$string['guardian_scheduler_iewarning'] = 'You appear to be using an old version of Internet Explorer, or be running a newer version in "Compatibility View".
    This page may not work correctly with old versions of Internet Explorer. Please upgrade to the latest version, or switch to a modern
    browser such as <a href="http://getfirefox.com">Firefox</a>.';
$string['guardian_scheduler_justmyschedule'] = 'Just show my appointments';
$string['guardian_scheduler_manage_sessions'] = 'Manage Guardian Sessions';
$string['guardian_scheduler_mustcorrect'] = 'You must correct this before you can book appointments';
$string['guardian_scheduler_newapp'] = 'Add a New Appointment';
$string['guardian_scheduler_new'] = 'New Parents\' scheduler';
$string['guardian_scheduler_noappointment'] = 'The specified appointment does not exist. It may already have been cancelled.';
$string['guardian_scheduler_noappointments'] = 'You have not booked any appointments, press \'Add a New Appointment\' then select a teacher and time';
$string['guardian_scheduler_noappointmentwith'] = 'There is no time entered for the appointment with ';
$string['guardian_scheduler_no'] = 'This Parents\' scheduler does not exist';
$string['guardian_scheduler_nos'] = 'No Parents\' schedulers have been created';
$string['guardian_scheduler_noparentname'] = 'You have not entered a parent\'s name';
$string['guardian_scheduler_nostudentname'] = 'You have not entered a student\'s name';
$string['guardian_scheduler_old'] = 'This parents scheduler has already taken place.';
$string['guardian_scheduler_parentname'] = 'Parent\'s name';
$string['guardian_scheduler_:manage'] = 'Manage Parents\' schedulers';
$string['guardian_scheduler_:book'] = 'Book Parents\' scheduler Appointments';
$string['guardian_scheduler_:cancel'] = 'Cancel Parents\' scheduler Appointments';
$string['guardian_scheduler_config'] = 'Parents scheduler Config';

$string['guardian_scheduler_info'] = 'Additional information';
$string['guardian_scheduler_on'] = 'Parents scheduler on {$a->date}';
$string['guardian_scheduler_schedule'] = 'Parents Scheduler Appointments';
$string['guardian_scheduler_available_teachers'] = 'Available Teachers';
$string['guardian_scheduler_bookable_teachers'] = 'Bookable Teachers';
$string['guardian_scheduler_disabled'] = 'Parents Scheduler features are currently disabled';
$string['guardian_scheduler_notfound'] = 'No Parents\' scheduler was found for the given ID';
$string['guardian_scheduler_parentname'] = 'Parent\'s name';
$string['guardian_scheduler_selectteacher'] = 'Select a Teacher...';
$string['guardian_scheduler_schedulefor'] = 'Parents scheduler schedule for {$a}';
$string['guardian_scheduler_studentname'] = 'Student\'s name';
$string['guardian_scheduler_success'] = '{$a} appontments have been successfully made';
$string['guardian_scheduler_teacher'] = 'Teacher';
$string['guardian_scheduler_timeend'] = 'End Time';
$string['guardian_scheduler_timestart'] = 'Start Time';
$string['guardian_scheduler_viewapps'] = 'View Appointments';

$string['guardian_scheduler_select_student'] = 'Please Select Student';
$string['guardian_scheduler_select_guardian'] = 'Please Select Guardian';
$string['guardian_scheduler_select_teacher'] = 'Please Select Teacher';
$string['guardian_scheduler_no_appointment_required'] = 'No Appointment Required';
$string['guardian_scheduler_no_guardians_found'] = 'No Guardians Found';
$string['guardian_scheduler_no_students_found'] = 'No Students Found';
