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
 * Defines the settings for the ZiLink block
 *
 * @package     local_zilink
 * @author      Ian Tasker <ian.tasker@schoolsict.net>
 * @copyright   2010 onwards SchoolsICT Limited, UK (http://schoolsict.net)
 * @copyright   Includes sub plugins that are based on and/or adapted from other plugins please see sub plugins for credits and notices. 
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$ADMIN->add('zilink_homework_report_settings', new admin_externalpage('zilink_homework_report_config_settings', get_string('config', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/homework/report/admin/config.php",'moodle/site:config'));  

 
if(file_exists($CFG->dirroot.'/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/settings.php'))
{
    $ADMIN->add('zilink_guardian_view_settings', new admin_category('zilink_homework_report_interface_settings', get_string('zilink_homework_report_interface_settings', 'local_zilink')));
    
    include($CFG->dirroot.'/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/settings.php');
    if(file_exists($CFG->dirroot.'/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/admin/defaults.php'))
    {
        include($CFG->dirroot.'/local/zilink/plugins/homework/report/interfaces/'.$CFG->zilink_homework_report_interface.'/admin/defaults.php');
    }
}