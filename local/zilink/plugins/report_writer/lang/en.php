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

$string['report_writer'] = 'Report Writer';
$string['report_writer_settings'] = $string['report_writer'];

$string['zilink:report_writer_view'] = $string['report_writer']. ' - View';
$string['zilink:report_writer_addinstance'] = $string['report_writer']. ' - Add Instance';
$string['zilink:report_writer_configure'] = $string['report_writer']. ' - Configure';
$string['zilink:report_senior_management_team_edit'] = $string['report_writer']. ' - Senior Management Edit';
$string['zilink:report_subject_leader_edit'] = $string['report_writer']. ' - Subject Leader Edit';
$string['zilink:report_subject_teacher_edit'] = $string['report_writer']. ' - Subject Teacher Edit';

$string['report_writer_page_title']                 = $string['zilink'].' '. $string['report_writer_settings'];
$string['report_writer_general_title']                      = 'General Settings';
$string['report_writer_general_title_desc']                 = 'Report Writer is a complete system enabling teachers to write reports that are published to parents/guardians within Guardian View. Any reports written are subject to a process of approval before they can be published to parents.';

$string['report_writer_manage_title']                      = 'Manage Reports';
$string['report_writer_manage_title_desc']                 = 'Report Writer is a complete system enabling teachers to write reports that are published to parents/guardians within Guardian View. Any reports written are subject to a process of approval before they can be published to parents.';

$string['report_writer_manage_reports'] = 'Manage Reports';

$string['report_writer_interface']                          = 'Interface';

$string['report_writer_support_desc'] = 'For more information about configuring the ZiLink Report Writer please see our ';
$string['report_writer_manage_reports'] = 'Manage Reports';

if(file_exists($CFG->dirroot.'/local/zilink/plugins/report_writer/interfaces/'.$CFG->zilink_report_writer_interface.'/lang/en.php'))
{
        include($CFG->dirroot.'/local/zilink/plugins/report_writer/interfaces/'.$CFG->zilink_report_writer_interface.'/lang/en.php');
}