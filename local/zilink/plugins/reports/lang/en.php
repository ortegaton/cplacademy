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

/* 
=============================================
    Moodle Permission Text
=============================================
*/

/*
==============================================
    ZiLink Block Text
==============================================
*/

$string['reports'] = 'Reports'; 

$string['reports_page_title'] = $string['zilink']. ' '.$string['reports'];
$string['reports_settings'] = 'Reports'; 
$string['reports_cohort_enrolment'] = 'Cohort Enrolment';

$string['reports_cohort_enrolment_title'] = 'Cohort Enrolment';  
$string['reports_cohort_enrolment_title_desc'] = 'This report lists all the courses that has a ZiLink cohort enroled.'; 

$string['reports_account_matching'] = 'Account Matching';
$string['reports_account_matching_title'] = 'Account Matching';  
$string['reports_account_matching_title_desc'] = 'This report tests all potential matching records to ensure that printable characters are used in AD or the school MIS in the Firstname and Lastname fields. If there unprintable or hidden characters are present, then records cannot be matched and the data needs to be cleansed';

$string['reports_timetable_weeks'] = 'Timetable Weeks';
$string['reports_timetable_weeks_title'] = 'Timetable Weeks';  
$string['reports_timetable_weeks_title_desc'] = 'This report shows how ZiLink is calculating week numbers for a given date.';

$string['reports_timetable_weeks_week_beginning'] = 'Week Beginning';
$string['reports_timetable_weeks_which_week'] = 'Which Week';
