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
 

$ADMIN->add('zilink_guardian_view_interface_settings', new admin_externalpage('zilink_guardian_view_default_general_settings', get_string('general', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/view/interfaces/default/admin/general.php",'moodle/site:config'));
$ADMIN->add('zilink_guardian_view_interface_settings', new admin_externalpage('zilink_guardian_view_default_attendance_settings', get_string('attendance', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/view/interfaces/default/admin/attendance.php",'moodle/site:config'));
$ADMIN->add('zilink_guardian_view_interface_settings', new admin_externalpage('zilink_guardian_view_default_homework_settings', get_string('homework', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/view/interfaces/default/admin/homework.php",'moodle/site:config'));

$ADMIN->add('zilink_guardian_view_interface_settings', new admin_category('zilink_guardian_view_default_interface_assessment_settings', get_string('assessment', 'local_zilink')));
$ADMIN->add('zilink_guardian_view_default_interface_assessment_settings', new admin_externalpage('zilink_guardian_view_default_assessment_overview_settings', get_string('overview', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/view/interfaces/default/admin/assessment_overview.php",'moodle/site:config'));
$ADMIN->add('zilink_guardian_view_default_interface_assessment_settings', new admin_externalpage('zilink_guardian_view_default_assessment_subjects_settings', get_string('subjects', 'local_zilink'), $CFG->httpswwwroot."/local/zilink/plugins/guardian/view/interfaces/default/admin/assessment_subjects.php",'moodle/site:config'));


