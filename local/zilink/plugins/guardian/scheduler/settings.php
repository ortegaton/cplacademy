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

$ADMIN->add('zilink_guardian_scheduler_settings', new admin_externalpage('zilink_guardian_scheduler_manage_settings', get_string('manage', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/scheduler/admin/manage.php",'moodle/site:config'));
//$ADMIN->add('zilink_guardian_scheduler_settings', new admin_externalpage('zilink_guardian_scheduler_edit_settings', get_string('zilink_guardian_scheduler_create_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/scheduler/admin/edit.php",'moodle/site:config'));
//$ADMIN->add('zilink_guardian_scheduler_settings', new admin_externalpage('zilink_guardian_scheduler_teachers_settings', get_string('zilink_guardian_scheduler_teachers_settings', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/scheduler/admin/teachers.php",'moodle/site:config'));
//'local/zilink:guardian_evening_manage'));  
//$ADMIN->add('zilink_guardian_view_settings', new admin_category('zilink_guardian_view_interface_settings', get_string('zilink_guardian_view_interface_settings', 'local_zilink')));
 