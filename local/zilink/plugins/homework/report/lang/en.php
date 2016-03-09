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

$string['homework_report'] = 'Homework Report';
$string['zilink_homework_report_settings'] = 'Report';

$string['zilink:homework_report_view'] = $string['homework_report']. ' - View';
$string['zilink:homework_report_addinstance'] = $string['homework_report']. ' - Add Instance';
$string['zilink:homework_report_senior_management_team'] = $string['homework_report']. ' - Senior Management View';
$string['zilink:homework_report_subject_leader'] = $string['homework_report']. ' - Subject Leader View';
$string['zilink:homework_report_subject_teacher'] = $string['homework_report']. ' - Subject Teacher View';

$string['homework_report_page_title']                 = $string['zilink'].' '. $string['homework_report'];
$string['homework_report_general_title']                      = 'General Settings';
$string['homework_report_general_title_desc']                 = 'Report Writer is a complete system enabling teachers to write reports that are published to parents/guardians within Guardian View. Any reports written are subject to a process of approval before they can be published to parents.';

$string['homework_report_manage_title']                      = 'Manage Reports';
$string['homework_report_manage_title_desc']                 = 'Report Writer is a complete system enabling teachers to write reports that are published to parents/guardians within Guardian View. Any reports written are subject to a process of approval before they can be published to parents.';

$string['homework_report_manage_reports'] = 'Manage Reports';

$string['homework_report_interface']                          = 'Interface';
$string['homework_report_filter']                          = 'Report Filter';
$string['datedue'] = 'Date Due';

$string['homework_select_cohort'] = 'Please select cohort to view set homework';
$string['timeframe'] = 'Timeframe';
$string['duedate'] = 'Date Due';

$string['homework_report_support_desc'] = 'For more information about configuring the ZiLink Report Writer please see our ';
$string['homework_report_manage_reports'] = 'Manage Reports';

if(!isset($CFG->zilink_homework_report_interface))
{
    $CFG->zilink_homework_report_interface = 'default';
}

if(file_exists($CFG->dirroot.'/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/lang/en.php'))
{
        include($CFG->dirroot.'/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/lang/en.php');
}