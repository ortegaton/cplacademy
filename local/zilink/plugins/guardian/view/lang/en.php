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

$string['guardian_view']                            = 'Guardian View';
$string['guardian_view_page_title']            = $string['zilink'] . ' '. $string['guardian_view']   ;


$string['zilink_guardian_view_settings']            = 'View';
$string['zilink_guardian_view_interface_settings']  = 'Interface';

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
$string['zilink:guardian_view_recent'] = 'Guardian View - View Welcome Tab';
$string['zilink:guardian_view_recent_attendance'] = 'Guardian View - Welcome Tab - View Attendance Panel';
$string['zilink:guardian_view_recent_behaviour'] = 'Guardian View - Welcome Tab - View Behaviour Panel';
$string['zilink:guardian_view_recent_assessment'] = 'Guardian View - Welcome Tab - View Assessment Panel';
$string['zilink:guardian_view_recent_home_learning'] = 'Guardian View - Welcome Tab - View Home Learning Panel';
$string['zilink:guardian_view_recent_behaviour'] = 'Guardian View - Welcome Tab - View Behaviour Panel';
$string['zilink:guardian_view_overview'] = 'Guardian View - View Overview Tab';
$string['zilink:guardian_view_overview_attendance'] = 'Guardian View - Overview Tab - View Attendance Panel';
$string['zilink:guardian_view_overview_assessment'] = 'Guardian View - Overview Tab - View Assessment Panel';
$string['zilink:guardian_view_overview_home_learning'] = 'Guardian View - Overview Tab - View Home Learning Panel';
$string['zilink:guardian_view_subjects'] = 'Guardian View - View Subjects Tab';
$string['zilink:guardian_view_subjects_teacher'] = 'Guardian View - Subjects Tab - View Teacher Information Panel';
$string['zilink:guardian_view_subjects_assessment'] = 'Guardian View - Subjects Tab - View Assessment Panel';
$string['zilink:guardian_view_subjects_home_learning'] = 'Guardian View - Subjects Tab - View Home Learning Panel';
$string['zilink:guardian_view_subjects_submitted_work'] = 'Guardian View - Subjects Tab - View Submitted Work Panel';
$string['zilink:guardian_view_timetable'] = 'Guardian View - Timetable Tab';
$string['zilink:guardian_view_information'] = 'Guardian View - Information Tab';


if(file_exists($CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces/'.$CFG->zilink_guardian_view_interface.'/lang/en.php'))
{
    include($CFG->dirroot.'/local/zilink/plugins/guardian/view/interfaces/'.$CFG->zilink_guardian_view_interface.'/lang/en.php');
}